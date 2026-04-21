<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviewService) {}

    /** GET /api/products/{productId}/reviews */
    public function index(string $productId): JsonResponse
    {
        $reviews = Review::where('product_id', $productId)
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    /** POST /api/products/{productId}/reviews */
    public function store(Request $request, string $productId): JsonResponse
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'title'   => 'sometimes|string|max:100',
            'content' => 'required|string|min:10|max:2000',
            'images'  => 'sometimes|array|max:5',
            'images.*'=> 'url',
        ]);

        // 동일 상품 중복 리뷰 방지 (비구매 리뷰도 1개만 허용)
        $exists = Review::where('product_id', $productId)
            ->where('user_id', $request->user()->id)
            ->whereNull('order_item_id')
            ->exists();

        if ($exists) {
            return response()->json(['message' => '이미 리뷰를 작성하셨습니다.'], 422);
        }

        try {
            $review = $this->reviewService->create($request->user()->id, $productId, $validated);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($review, 201);
    }

    /** DELETE /api/reviews/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = Review::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->reviewService->delete($review);

        return response()->json(null, 204);
    }
}
