<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;

class ReviewService
{
    /**
     * 구매 확인 후 리뷰 작성
     */
    public function create(int $userId, int $productId, array $data): Review
    {
        // 구매 이력 확인 — paid / shipping / delivered 상태의 주문에서 해당 상품을 샀는지
        $orderItem = OrderItem::where('product_id', $productId)
            ->whereHas('order', fn ($q) => $q->where('user_id', $userId)->whereIn('status', ['paid', 'shipping', 'delivered']))
            ->whereDoesntHave('review') // 이미 리뷰 쓴 order_item 제외
            ->latest()
            ->first();

        $review = Review::create([
            'product_id'    => $productId,
            'user_id'       => $userId,
            'order_item_id' => $orderItem?->id,
            'rating'        => $data['rating'],
            'title'         => $data['title'] ?? null,
            'content'       => $data['content'],
            'images'        => $data['images'] ?? [],
            'is_verified'   => $orderItem !== null,
        ]);

        $this->recalcRating($productId);

        return $review->load('user:id,name');
    }

    /**
     * 리뷰 삭제 후 평점 재계산
     */
    public function delete(Review $review): void
    {
        $productId = $review->product_id;
        $review->delete();
        $this->recalcRating($productId);
    }

    private function recalcRating(int $productId): void
    {
        $agg = Review::where('product_id', $productId)
            ->selectRaw('AVG(rating) as avg_r, COUNT(*) as cnt')
            ->first();

        Product::where('id', $productId)->update([
            'rating_avg' => round((float) ($agg->avg_r ?? 0), 2),
        ]);
    }
}
