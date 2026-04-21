<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchService
{
    private string $host;
    private string $index;

    public function __construct()
    {
        $this->host  = rtrim(config('services.elasticsearch.host', 'http://elasticsearch:9200'), '/');
        $this->index = config('services.elasticsearch.index', 'zslab_products');
    }

    /* ── 검색 (ES → DB fallback) ─────────────────────────────────────────── */

    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        try {
            return $this->searchEs($query, $page, $perPage);
        } catch (\Throwable $e) {
            Log::warning('Elasticsearch unavailable, falling back to DB.', ['error' => $e->getMessage()]);
            return $this->searchDb($query, $page, $perPage);
        }
    }

    private function searchEs(string $query, int $page, int $perPage): array
    {
        $body = [
            'from' => ($page - 1) * $perPage,
            'size' => $perPage,
            'query' => [
                'bool' => [
                    'must'   => [
                        'multi_match' => [
                            'query'    => $query,
                            'fields'   => ['name^3', 'description^1', 'category_name^2'],
                            'operator' => 'or',
                        ],
                    ],
                    'filter' => [
                        ['term' => ['status' => 'active']],
                    ],
                ],
            ],
            'sort' => [
                ['_score' => 'desc'],
                ['order_count' => 'desc'],
            ],
        ];

        $response = Http::timeout(3)
            ->post("{$this->host}/{$this->index}/_search", $body);

        if ($response->failed()) {
            throw new \RuntimeException('ES search failed: ' . $response->status());
        }

        $json  = $response->json();
        $hits  = $json['hits']['hits'] ?? [];
        $total = $json['hits']['total']['value'] ?? 0;

        // ES 결과에서 product_id 추출 → DB에서 최신 데이터로 보강
        $ids = array_map(fn ($h) => $h['_id'], $hits);
        if (empty($ids)) {
            return $this->paginate([], $total, $page, $perPage);
        }

        $products = Product::with('category')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        // ES 정렬 순서 유지
        $ordered = array_values(array_filter(
            array_map(fn ($id) => $products->get($id)?->toArray(), $ids)
        ));

        return $this->paginate($ordered, $total, $page, $perPage);
    }

    private function searchDb(string $query, int $page, int $perPage): array
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

        return $this->paginate($products, $total, $page, $perPage);
    }

    /* ── 인덱싱 ──────────────────────────────────────────────────────────── */

    public function indexProduct(Product $product): void
    {
        $doc = [
            'id'            => $product->id,
            'name'          => $product->name,
            'slug'          => $product->slug,
            'description'   => $product->description ?? '',
            'category_name' => $product->category?->name ?? '',
            'price'         => $product->price,
            'sale_price'    => $product->sale_price,
            'status'        => $product->status,
            'order_count'   => $product->order_count,
            'rating_avg'    => $product->rating_avg,
            'images'        => $product->images ?? [],
        ];

        Http::timeout(5)
            ->put("{$this->host}/{$this->index}/_doc/{$product->id}", $doc);
    }

    public function deleteProduct(int $productId): void
    {
        Http::timeout(3)
            ->delete("{$this->host}/{$this->index}/_doc/{$productId}");
    }

    public function ensureIndex(): void
    {
        $url = "{$this->host}/{$this->index}";

        $exists = Http::timeout(5)->head($url);
        if ($exists->successful()) {
            return; // 이미 존재
        }

        Http::timeout(5)->put($url, [
            'settings' => [
                'number_of_shards'   => 1,
                'number_of_replicas' => 0,
                'max_ngram_diff'     => 9,   // max_gram(10) - min_gram(2) - 1
                'analysis' => [
                    'tokenizer' => [
                        'ngram_tokenizer' => [
                            'type'        => 'ngram',
                            'min_gram'    => 2,
                            'max_gram'    => 10,
                            'token_chars' => ['letter', 'digit'],
                        ],
                    ],
                    'analyzer' => [
                        // 인덱싱: ngram으로 모든 부분 문자열 토큰화
                        'korean_ngram' => [
                            'type'      => 'custom',
                            'tokenizer' => 'ngram_tokenizer',
                            'filter'    => ['lowercase'],
                        ],
                        // 검색: 입력어를 그대로 토큰화 (ngram 미적용)
                        'korean_search' => [
                            'type'      => 'custom',
                            'tokenizer' => 'standard',
                            'filter'    => ['lowercase'],
                        ],
                    ],
                ],
            ],
            'mappings' => [
                'properties' => [
                    'id'            => ['type' => 'integer'],
                    'name'          => ['type' => 'text', 'analyzer' => 'korean_ngram', 'search_analyzer' => 'korean_search'],
                    'slug'          => ['type' => 'keyword'],
                    'description'   => ['type' => 'text', 'analyzer' => 'korean_ngram', 'search_analyzer' => 'korean_search'],
                    'category_name' => ['type' => 'text', 'analyzer' => 'korean_ngram', 'search_analyzer' => 'korean_search'],
                    'price'         => ['type' => 'integer'],
                    'sale_price'    => ['type' => 'integer'],
                    'status'        => ['type' => 'keyword'],
                    'order_count'   => ['type' => 'integer'],
                    'rating_avg'    => ['type' => 'float'],
                    'images'        => ['type' => 'keyword', 'index' => false],
                ],
            ],
        ]);
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
