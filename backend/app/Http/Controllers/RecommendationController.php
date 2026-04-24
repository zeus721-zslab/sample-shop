<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    private string $esHost;
    private string $esIndex;

    public function __construct()
    {
        $this->esHost  = rtrim(config('services.elasticsearch.host', 'http://elasticsearch:9200'), '/');
        $this->esIndex = config('services.elasticsearch.index', 'zslab_products');
    }

    /** GET /api/recommendations */
    public function index(Request $request): JsonResponse
    {
        // Sanctum 토큰이 있으면 사용자 로드 (없어도 에러 안 냄)
        $user = null;
        if ($request->bearerToken()) {
            try {
                auth()->setDefaultDriver('sanctum');
                $user = auth()->user();
            } catch (\Throwable) {
                // 토큰 무효 → null 유지
            }
        }

        if (! $user) {
            return response()->json(['products' => $this->popularProducts(), 'type' => 'popular']);
        }

        try {
            $products = $this->personalizedRecommendations($user);

            if (count($products) < 6) {
                // 부족하면 인기 상품으로 보완
                $existingIds = array_column($products, 'id');
                $popular     = $this->popularProducts(12, $existingIds);
                $products    = array_merge($products, array_slice($popular, 0, 12 - count($products)));
            }

            return response()->json(['products' => array_slice($products, 0, 12), 'type' => 'personalized']);
        } catch (\Throwable $e) {
            Log::warning('Recommendation failed, falling back to popular.', ['error' => $e->getMessage()]);
            return response()->json(['products' => $this->popularProducts(), 'type' => 'popular']);
        }
    }

    /**
     * 개인화 추천: 구매 이력 + 협업 필터링 + 세그먼트
     */
    private function personalizedRecommendations(object $user): array
    {
        $recommended = [];
        $excludeIds  = [];

        // 이미 구매한 상품 ID 목록
        $boughtProductIds = OrderItem::whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->pluck('product_id')
            ->unique()
            ->values()
            ->toArray();

        $excludeIds = $boughtProductIds;

        // 1. 구매 이력 기반 추천 (카테고리 기반)
        if (! empty($boughtProductIds)) {
            $categoryIds = Product::whereIn('id', $boughtProductIds)
                ->pluck('category_id')
                ->unique()
                ->values()
                ->toArray();

            if (! empty($categoryIds)) {
                $catProducts = $this->recommendByCategories($categoryIds, $excludeIds, 6);
                $recommended = array_merge($recommended, $catProducts);
                $excludeIds  = array_merge($excludeIds, array_column($catProducts, 'id'));
            }
        }

        // 2. 협업 필터링: 같은 상품 구매한 유저들의 다른 구매 상품
        if (! empty($boughtProductIds) && count($recommended) < 8) {
            $collab = $this->collaborativeFiltering($user->id, $boughtProductIds, $excludeIds, 4);
            $recommended = array_merge($recommended, $collab);
            $excludeIds  = array_merge($excludeIds, array_column($collab, 'id'));
        }

        // 3. 성별/나이 기반 세그먼트 추천
        if (count($recommended) < 8 && ($user->gender || $user->birth_year)) {
            $segment = $this->segmentRecommendations($user, $excludeIds, 4);
            $recommended = array_merge($recommended, $segment);
        }

        return $recommended;
    }

    /**
     * 카테고리 기반 추천 (Elasticsearch)
     */
    private function recommendByCategories(array $categoryIds, array $excludeIds, int $size): array
    {
        try {
            $body = [
                'size'  => $size,
                'query' => [
                    'bool' => [
                        'must'    => [['terms' => ['category_id' => $categoryIds]]],
                        'filter'  => [['term' => ['status' => 'active']]],
                        'must_not' => empty($excludeIds) ? [] : [['ids' => ['values' => array_map('strval', $excludeIds)]]],
                    ],
                ],
                'sort' => [['order_count' => 'desc'], ['rating_avg' => 'desc']],
            ];

            $response = Http::timeout(3)->post("{$this->esHost}/{$this->esIndex}/_search", $body);

            if ($response->failed()) {
                return $this->recommendByCategoriesDb($categoryIds, $excludeIds, $size);
            }

            $hits = $response->json()['hits']['hits'] ?? [];
            $ids  = array_map(fn ($h) => (int) $h['_id'], $hits);

            return $this->hydrateProducts($ids);
        } catch (\Throwable) {
            return $this->recommendByCategoriesDb($categoryIds, $excludeIds, $size);
        }
    }

    private function recommendByCategoriesDb(array $categoryIds, array $excludeIds, int $size): array
    {
        return Product::whereIn('category_id', $categoryIds)
            ->where('status', 'active')
            ->when(! empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->orderByDesc('order_count')
            ->take($size)
            ->get()
            ->toArray();
    }

    /**
     * 협업 필터링 (DB 기반)
     */
    private function collaborativeFiltering(int $userId, array $boughtProductIds, array $excludeIds, int $size): array
    {
        // 같은 상품을 구매한 다른 사용자 ID
        $similarUserIds = OrderItem::whereIn('product_id', $boughtProductIds)
            ->whereHas('order', fn ($q) => $q->where('user_id', '!=', $userId))
            ->select('orders.user_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->distinct()
            ->limit(50)
            ->pluck('orders.user_id')
            ->toArray();

        if (empty($similarUserIds)) {
            return [];
        }

        // 그 사용자들이 구매한 상품 중 내가 안 산 것
        $productIds = OrderItem::whereHas('order', fn ($q) => $q->whereIn('user_id', $similarUserIds))
            ->whereNotIn('product_id', array_merge($boughtProductIds, $excludeIds))
            ->select('product_id', DB::raw('COUNT(*) as buy_count'))
            ->groupBy('product_id')
            ->orderByDesc('buy_count')
            ->limit($size)
            ->pluck('product_id')
            ->toArray();

        return $this->hydrateProducts($productIds);
    }

    /**
     * 성별/나이 세그먼트 기반 인기 상품
     */
    private function segmentRecommendations(object $user, array $excludeIds, int $size): array
    {
        $query = Order::where('status', 'delivered')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.delivered_at', '>=', now()->subMonths(3));

        // 성별 필터
        if ($user->gender) {
            $query->where('users.gender', $user->gender);
        }

        // 나이대 필터 (±5년)
        if ($user->birth_year) {
            $query->whereBetween('users.birth_year', [$user->birth_year - 5, $user->birth_year + 5]);
        }

        $productIds = $query
            ->whereNotIn('order_items.product_id', $excludeIds)
            ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('order_items.product_id')
            ->orderByDesc('total_qty')
            ->limit($size)
            ->pluck('order_items.product_id')
            ->toArray();

        return $this->hydrateProducts($productIds);
    }

    /**
     * 인기 상품 (fallback)
     */
    private function popularProducts(int $size = 12, array $excludeIds = []): array
    {
        return Product::with('category')
            ->where('status', 'active')
            ->when(! empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->orderByDesc('order_count')
            ->take($size)
            ->get()
            ->toArray();
    }

    /**
     * ID 배열 → Product 모델 배열 (순서 유지)
     */
    private function hydrateProducts(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $products = Product::with('category')
            ->whereIn('id', $ids)
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        return array_values(array_filter(
            array_map(fn ($id) => $products->get($id)?->toArray(), $ids)
        ));
    }
}
