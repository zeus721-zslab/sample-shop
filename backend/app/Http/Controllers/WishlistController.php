<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /** GET /api/wishlist */
    public function index(Request $request): JsonResponse
    {
        $items = Wishlist::where('user_id', $request->user()->id)
            ->with('product:id,name,slug,price,sale_price,images,status,category_id')
            ->latest()
            ->get();

        return response()->json($items);
    }

    /** POST /api/wishlist/{productId} — 토글 (없으면 추가, 있으면 제거) */
    public function toggle(Request $request, int $productId): JsonResponse
    {
        $userId = $request->user()->id;

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['wishlisted' => false]);
        }

        Wishlist::create(['user_id' => $userId, 'product_id' => $productId]);
        return response()->json(['wishlisted' => true], 201);
    }

    /** DELETE /api/wishlist/{productId} */
    public function destroy(Request $request, int $productId): JsonResponse
    {
        $deleted = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => '위시리스트에 없는 상품입니다.'], 404);
        }

        return response()->json(null, 204);
    }

    /** GET /api/wishlist/check/{productId} — 위시 여부 확인 */
    public function check(Request $request, int $productId): JsonResponse
    {
        $wishlisted = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json(['wishlisted' => $wishlisted]);
    }
}
