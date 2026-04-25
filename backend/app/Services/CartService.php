<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class CartService
{
    private function key(int $userId): string
    {
        return "cart:{$userId}";
    }

    /** 장바구니 전체 조회 */
    public function all(int $userId): array
    {
        $raw = Redis::hgetall($this->key($userId));

        if (empty($raw)) {
            return ['items' => [], 'count' => 0, 'subtotal' => 0];
        }

        // N+1 방지: 상품 ID 한번에 수집 후 IN 쿼리로 일괄 조회
        $decoded = [];
        foreach ($raw as $cartItemId => $json) {
            $decoded[$cartItemId] = json_decode($json, true);
        }

        $productIds = array_unique(array_column(array_values($decoded), 'product_id'));
        $products   = Product::select('id', 'name', 'slug', 'price', 'sale_price', 'images', 'stock', 'status')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($decoded as $cartItemId => $item) {
            $product = $products->get($item['product_id']);

            if (! $product || $product->status === 'inactive') {
                continue; // 삭제/비활성 상품 제외
            }

            $item['cart_item_id']    = $cartItemId;
            $item['name']            = $product->name;
            $item['slug']            = $product->slug;
            $item['price']           = $product->price;
            $item['sale_price']      = $product->sale_price;
            $item['effective_price'] = $product->sale_price ?? $product->price;
            $item['image']           = $product->images[0] ?? null;
            $item['stock']           = $product->stock;
            $item['is_soldout']      = $product->status === 'soldout';
            $item['line_total']      = $item['effective_price'] * $item['quantity'];

            $items[] = $item;
        }

        $subtotal = array_sum(array_column($items, 'line_total'));

        return [
            'items'     => $items,
            'count'     => count($items),
            'subtotal'  => $subtotal,
        ];
    }

    /** 상품 추가 또는 수량 변경 */
    public function add(int $userId, int $productId, int $quantity, array $options = []): array
    {
        $product = Product::findOrFail($productId);

        if ($product->status === 'inactive') {
            throw new \RuntimeException('판매하지 않는 상품입니다.');
        }

        $key = $this->key($userId);

        // 이미 담긴 동일 상품(+옵션)이 있으면 수량 합산
        $existing = Redis::hgetall($key);
        foreach ($existing as $cartItemId => $json) {
            $item = json_decode($json, true);
            if ($item['product_id'] === $productId && $item['options'] === $options) {
                $item['quantity'] = min($item['quantity'] + $quantity, $product->stock ?: 999);
                Redis::hset($key, $cartItemId, json_encode($item));
                Redis::expire($key, 60 * 60 * 24 * 30); // 30일
                return ['cart_item_id' => $cartItemId] + $item;
            }
        }

        // 새 아이템
        $cartItemId = (string) Str::uuid();
        $item = [
            'product_id' => $productId,
            'quantity'   => min($quantity, $product->stock ?: 999),
            'options'    => $options,
            'added_at'   => now()->toISOString(),
        ];

        Redis::hset($key, $cartItemId, json_encode($item));
        Redis::expire($key, 60 * 60 * 24 * 30);

        return ['cart_item_id' => $cartItemId] + $item;
    }

    /** 수량 변경 */
    public function update(int $userId, string $cartItemId, int $quantity): bool
    {
        $key  = $this->key($userId);
        $json = Redis::hget($key, $cartItemId);

        if (! $json) {
            return false;
        }

        $item = json_decode($json, true);
        $product = Product::find($item['product_id']);
        $item['quantity'] = min($quantity, $product?->stock ?: 999);

        Redis::hset($key, $cartItemId, json_encode($item));
        Redis::expire($key, 60 * 60 * 24 * 30);

        return true;
    }

    /** 아이템 삭제 */
    public function remove(int $userId, string $cartItemId): bool
    {
        return (bool) Redis::hdel($this->key($userId), $cartItemId);
    }

    /** 장바구니 비우기 */
    public function clear(int $userId): void
    {
        Redis::del($this->key($userId));
    }

    /** 주문 후 구매 확정 아이템만 제거 */
    public function removeMany(int $userId, array $cartItemIds): void
    {
        if (empty($cartItemIds)) return;
        Redis::hdel($this->key($userId), ...$cartItemIds);
    }
}
