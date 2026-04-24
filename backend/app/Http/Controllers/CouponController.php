<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * POST /api/coupons/validate
     * 쿠폰 유효성 확인 + 할인 금액 반환
     */
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'         => 'required|string|max:50',
            'order_amount' => 'required|integer|min:0',
        ]);

        try {
            [$coupon, $discount] = $this->orderService->applyCoupon(
                $validated['code'],
                $validated['order_amount'],
                $request->user()->id,
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'valid'           => true,
            'coupon_name'     => $coupon->name,
            'type'            => $coupon->type,
            'value'           => $coupon->value,
            'discount_amount' => $discount,
            'final_amount'    => max(0, $validated['order_amount'] - $discount),
        ]);
    }
}
