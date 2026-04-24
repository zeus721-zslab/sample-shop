<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MyController extends Controller
{
    /** GET /api/my/profile */
    public function profile(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /** PATCH /api/my/profile */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'sometimes|string|max:100',
            'phone'            => 'sometimes|nullable|string|max:20',
            'current_password' => 'required_with:password|string',
            'password'         => ['sometimes', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        // 비밀번호 변경 시 현재 비밀번호 확인
        if (isset($validated['password'])) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => '현재 비밀번호가 올바르지 않습니다.'], 422);
            }
            $user->password = Hash::make($validated['password']);
        }

        if (isset($validated['name']))  $user->name  = $validated['name'];
        if (array_key_exists('phone', $validated)) $user->phone = $validated['phone'];

        $user->save();

        return response()->json($user);
    }

    /** GET /api/my/orders */
    public function orders(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items' => fn ($q) => $q->select('id', 'order_id', 'product_id', 'product_name', 'product_image', 'quantity', 'unit_price', 'total_price', 'options')])
            ->select('id', 'order_number', 'status', 'total_amount', 'discount_amount', 'final_amount', 'coupon_code', 'payment_method', 'paid_at', 'shipped_at', 'delivered_at', 'created_at')
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    /** GET /api/my/reviews */
    public function reviews(Request $request): JsonResponse
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with('product:id,name,slug,images')
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    /** GET /api/my/wishlist — 위시리스트 (단축) */
    public function wishlist(Request $request): JsonResponse
    {
        $items = $request->user()
            ->wishlists()
            ->with('product:id,name,slug,price,sale_price,images,status')
            ->latest()
            ->paginate(20);

        return response()->json($items);
    }
}
