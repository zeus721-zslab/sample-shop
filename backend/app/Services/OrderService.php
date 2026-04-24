<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    private const MAX_RETRY = 3;

    public function __construct(
        private CartService             $cart,
        private PaymentGatewayInterface $payment,
    ) {}

    /**
     * 주문 생성 → 결제 준비 (낙관적 락으로 재고 Race Condition 방지)
     */
    public function create(
        int    $userId,
        array  $cartItemIds,
        array  $shippingAddress,
        string $couponCode = '',
    ): array {
        // 1. 장바구니 조회
        $cartData = $this->cart->all($userId);
        $items    = $cartData['items'] ?? [];

        if (empty($items)) {
            throw new \RuntimeException('장바구니가 비어 있습니다.');
        }

        if (! empty($cartItemIds)) {
            $items = array_values(array_filter($items, fn ($i) => in_array($i['cart_item_id'], $cartItemIds)));
        }

        if (empty($items)) {
            throw new \RuntimeException('주문할 상품이 없습니다.');
        }

        // 2. 금액 계산
        $totalAmount = array_sum(array_map(fn ($i) => $i['effective_price'] * $i['quantity'], $items));

        // 3. 쿠폰 처리
        $coupon         = null;
        $discountAmount = 0;

        if ($couponCode !== '') {
            [$coupon, $discountAmount] = $this->applyCoupon($couponCode, $totalAmount, $userId);
        }

        $finalAmount = max(0, $totalAmount - $discountAmount);

        // 4. 재고 차감 (낙관적 락 + 재시도)
        $order = $this->createWithOptimisticLock(
            $userId, $items, $shippingAddress,
            $totalAmount, $discountAmount, $finalAmount, $couponCode,
        );

        // 5. 쿠폰 사용 이력 저장
        if ($coupon !== null) {
            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'user_id'   => $userId,
                'order_id'  => $order->id,
            ]);
            $coupon->increment('used_count');
        }

        // 6. 결제 준비
        $user        = $order->user;
        $paymentData = $this->payment->prepare([
            'amount'       => $finalAmount,
            'order_number' => $order->order_number,
            'buyer_name'   => $user->name,
            'buyer_email'  => $user->email,
        ]);

        $order->update(['payment_id' => $paymentData['payment_id']]);

        // 6b. Mock 결제 (next_action=none): 즉시 paid 처리
        if (($paymentData['next_action'] ?? '') === 'none') {
            $confirmResult = $this->payment->confirm($paymentData['payment_id'], $finalAmount);
            if ($confirmResult['success']) {
                $order->update([
                    'status'      => 'paid',
                    'paid_amount' => $confirmResult['paid_amount'],
                    'paid_at'     => now(),
                    'payment_raw' => $confirmResult['raw'] ?? null,
                ]);
            }
        }

        // 7. 장바구니에서 주문 아이템 제거
        $this->cart->removeMany($userId, array_column($items, 'cart_item_id'));

        return [
            'order'   => $order->fresh('items'),
            'payment' => $paymentData,
        ];
    }

    /**
     * 쿠폰 유효성 검증 및 할인 금액 계산.
     * 성공 시 [Coupon, discountAmount] 반환.
     * 실패 시 RuntimeException throw.
     */
    public function applyCoupon(string $code, int $totalAmount, int $userId): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            throw new \RuntimeException('존재하지 않는 쿠폰 코드입니다.');
        }

        if (! $coupon->isValid()) {
            throw new \RuntimeException('사용할 수 없는 쿠폰입니다. (만료 또는 소진)');
        }

        if ($totalAmount < $coupon->min_order_amount) {
            throw new \RuntimeException(
                number_format($coupon->min_order_amount) . '원 이상 주문 시 사용 가능한 쿠폰입니다.'
            );
        }

        // 동일 쿠폰 중복 사용 방지
        $alreadyUsed = CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyUsed) {
            throw new \RuntimeException('이미 사용한 쿠폰입니다.');
        }

        // 할인 금액 계산
        if ($coupon->type === 'fixed') {
            $discount = (int) $coupon->value;
        } else {
            // percent
            $discount = (int) round($totalAmount * $coupon->value / 100);
            if ($coupon->max_discount_amount) {
                $discount = min($discount, (int) $coupon->max_discount_amount);
            }
        }

        return [$coupon, $discount];
    }

    /**
     * 낙관적 락(stock_version)을 사용해 재고를 차감하고 주문을 생성.
     * 버전 불일치 시 최대 MAX_RETRY 회 재시도.
     */
    private function createWithOptimisticLock(
        int    $userId,
        array  $items,
        array  $shippingAddress,
        int    $totalAmount,
        int    $discountAmount,
        int    $finalAmount,
        string $couponCode,
    ): Order {
        $lastError = null;

        for ($attempt = 1; $attempt <= self::MAX_RETRY; $attempt++) {
            // 현재 stock_version 스냅샷
            $productIds = array_column($items, 'product_id');
            $versions   = Product::whereIn('id', $productIds)
                ->pluck('stock_version', 'id');

            try {
                $order = DB::transaction(function () use (
                    $userId, $items, $shippingAddress,
                    $totalAmount, $discountAmount, $finalAmount,
                    $couponCode, $versions,
                ) {
                    foreach ($items as $item) {
                        $productId = $item['product_id'];
                        $version   = $versions[$productId] ?? 0;

                        // 낙관적 락: stock_version 일치 + 충분한 재고일 때만 차감
                        $affected = Product::where('id', $productId)
                            ->where('stock_version', $version)
                            ->where('stock', '>=', $item['quantity'])
                            ->update([
                                'stock'         => DB::raw("stock - {$item['quantity']}"),
                                'stock_version' => $version + 1,
                            ]);

                        if ($affected === 0) {
                            // 재고 부족인지 버전 충돌인지 구분
                            $product = Product::find($productId);
                            if (! $product || $product->stock < $item['quantity']) {
                                throw new \RuntimeException(
                                    ($product->name ?? '상품') . ' 재고가 부족합니다.'
                                );
                            }
                            // 버전 충돌 — 재시도 신호
                            throw new \RuntimeException('__version_conflict__');
                        }

                        // 품절 처리
                        Product::where('id', $productId)->where('stock', '<=', 0)
                            ->update(['status' => 'soldout']);
                    }

                    // 주문 + 아이템 생성
                    $order = Order::create([
                        'user_id'          => $userId,
                        'order_number'     => $this->generateOrderNumber(),
                        'status'           => 'pending',
                        'total_amount'     => $totalAmount,
                        'discount_amount'  => $discountAmount,
                        'final_amount'     => $finalAmount,
                        'paid_amount'      => 0,
                        'shipping_address' => $shippingAddress,
                        'coupon_code'      => $couponCode ?: null,
                        'payment_method'   => 'online',
                    ]);

                    foreach ($items as $item) {
                        $lineTotal = $item['effective_price'] * $item['quantity'];
                        OrderItem::create([
                            'order_id'      => $order->id,
                            'product_id'    => $item['product_id'],
                            'product_name'  => $item['name'],
                            'product_image' => $item['image'],
                            'options'       => $item['options'] ?? [],
                            'quantity'      => $item['quantity'],
                            'unit_price'    => $item['effective_price'],
                            'subtotal'      => $lineTotal,
                            'total_price'   => $lineTotal,
                        ]);
                    }

                    return $order;
                });

                // 트랜잭션 성공
                return $order;

            } catch (\RuntimeException $e) {
                if ($e->getMessage() !== '__version_conflict__') {
                    throw $e;
                }
                $lastError = $e;
                usleep(50_000 * $attempt); // 50ms, 100ms, 150ms
            }
        }

        throw new \RuntimeException('재고 처리 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.');
    }

    /**
     * 결제 완료 처리
     */
    public function confirm(int $orderId, int $userId): Order
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        $result = $this->payment->confirm($order->payment_id, $order->final_amount);

        if (! $result['success']) {
            throw new \RuntimeException('결제 금액 검증 실패. 결제가 취소되었습니다.');
        }

        $order->update([
            'status'      => 'paid',
            'paid_at'     => now(),
            'payment_raw' => $result['raw'],
        ]);

        return $order->fresh('items');
    }

    /**
     * 배송 상태 업데이트
     *
     * 허용 전환: paid → shipping → delivered
     *           paid/pending → cancelled (재고 복원 + 쿠폰 복원)
     */
    public function updateStatus(int $orderId, int $userId, string $newStatus): Order
    {
        $allowed = [
            'paid'     => ['shipping', 'cancelled'],
            'pending'  => ['cancelled'],
            'shipping' => ['delivered'],
        ];

        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if (! in_array($newStatus, $allowed[$order->status] ?? [])) {
            throw new \RuntimeException("'{$order->status}' 상태에서 '{$newStatus}'(으)로 변경할 수 없습니다.");
        }

        $timestamps = match ($newStatus) {
            'shipping'  => ['shipped_at' => now()],
            'delivered' => ['delivered_at' => now()],
            default     => [],
        };

        DB::transaction(function () use ($order, $newStatus, $timestamps) {
            if ($newStatus === 'cancelled') {
                // 재고 복원
                foreach ($order->items as $item) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                    Product::where('id', $item->product_id)->where('status', 'soldout')
                        ->update(['status' => 'active']);
                }

                // 쿠폰 복원 (사용 이력 삭제 + used_count 차감)
                if ($order->coupon_code) {
                    $usage = CouponUsage::where('order_id', $order->id)->first();
                    if ($usage) {
                        Coupon::where('id', $usage->coupon_id)->decrement('used_count');
                        $usage->delete();
                    }
                }
            }

            $order->update(array_merge(['status' => $newStatus], $timestamps));
        });

        return $order->fresh('items');
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
    }
}
