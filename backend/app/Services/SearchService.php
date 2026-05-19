<?php

namespace App\Services;

use App\Models\Product;
use Zslab\Search\Search\PaginatedResult;
use Zslab\Search\Search\SearchBuilder;
use Zslab\Search\Search\SuggestBuilder;
use Illuminate\Support\Facades\Log;

class SearchService
{
    public function __construct(
        private SearchBuilder  $searchBuilder,
        private SuggestBuilder $suggestBuilder,
    ) {}

    /* ── 검색 (ES → DB fallback) ─────────────────────────────────────────── */

    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        $result = $this->searchBuilder
            ->index(Product::getSearchIndex())
            ->fields(Product::getSearchFields())
            ->query($query)
            ->fuzzyField('name_jamo')
            ->filter(['status' => 'active'])
            ->page($page, $perPage)
            ->fallback(function () use ($query, $page, $perPage): PaginatedResult {
                Log::warning('Elasticsearch unavailable, falling back to DB.');
                return $this->dbSearchAsPaginatedResult($query, $page, $perPage);
            })
            ->search();

        // ES가 결과를 반환한 경우 → IDs로 DB 재조회 (최신 데이터 보장, ES 정렬 유지)
        if (!empty($result->ids)) {
            $products = Product::with('category')
                ->whereIn('id', $result->ids)
                ->get()
                ->keyBy('id');

            $ordered = array_values(array_filter(
                array_map(fn($id) => $products->get($id)?->toArray(), $result->ids)
            ));

            return $this->paginate($ordered, $result->total, $page, $perPage);
        }

        // DB fallback 또는 genuine empty 결과
        // fallback이 호출된 경우 $result->data 에 DB 데이터가 담겨 있음
        return $this->paginate($result->data, $result->total, $page, $perPage);
    }

    private function dbSearchAsPaginatedResult(string $query, int $page, int $perPage): PaginatedResult
    {
        $q = Product::with('category')
            ->where('status', 'active')
            ->where(function ($qb) use ($query) {
                $qb->where('name', 'like', "%{$query}%")
                   ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderByDesc('order_count');

        $total    = $q->count();
        $products = $q->forPage($page, $perPage)->get()->toArray();

        return new PaginatedResult(
            total:       $total,
            currentPage: $page,
            perPage:     $perPage,
            data:        $products,
            ids:         [],
        );
    }

    /* ── 자동완성 (suggest) ─────────────────────────────────────────────── */

    public function suggest(string $query, int $size = 5): array
    {
        $hits = $this->suggestBuilder
            ->index(Product::getSearchIndex())
            ->field('name_suggest')
            ->fuzzyField('name_jamo')
            ->query($query)
            ->size($size)
            ->suggest();

        // SuggestBuilder는 ES 장애 시 빈 배열 반환 → DB fallback
        if (empty($hits)) {
            return $this->dbFallbackSuggest($query, $size);
        }

        return array_map(fn($src) => [
            'id'    => (int) ($src['id'] ?? 0),
            'name'  => $src['name'] ?? '',
            'slug'  => $src['slug'] ?? '',
            'image' => $src['images'][0] ?? null,
        ], $hits);
    }

    private function dbFallbackSuggest(string $query, int $size): array
    {
        return Product::where('status', 'active')
            ->where('name', 'like', "%{$query}%")
            ->orderByDesc('order_count')
            ->limit($size)
            ->get(['id', 'name', 'slug', 'images'])
            ->map(fn($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'slug'  => $p->slug,
                'image' => $p->images[0] ?? null,
            ])
            ->values()
            ->toArray();
    }

    /* ── 유틸 ────────────────────────────────────────────────────────────── */

    private function paginate(array $data, int $total, int $page, int $perPage): array
    {
        return [
            'data'         => $data,
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => max(1, (int) ceil($total / $perPage)),
        ];
    }
}
