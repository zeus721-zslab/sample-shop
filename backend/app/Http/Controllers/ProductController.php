<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products
     *
     * Query params:
     *   category  - category slug
     *   search    - keyword (name/description)
     *   min_price, max_price
     *   sort      - latest(default)|price_asc|price_desc|popular|rating
     *   per_page  - default 20, max 100
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::active()
            ->with(['category:id,name,slug', 'seller:id,name,shop_name']);

        if ($category = $request->get('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($min = $request->get('min_price')) {
            $query->where('price', '>=', (int) $min);
        }

        if ($max = $request->get('max_price')) {
            $query->where('price', '<=', (int) $max);
        }

        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular'    => $query->orderByDesc('order_count'),
            'rating'     => $query->orderByDesc('rating_avg'),
            default      => $query->latest(),
        };

        $perPage = min((int) $request->get('per_page', 20), 100);
        $products = $query->paginate($perPage);

        // append computed attributes
        $products->through(function ($p) {
            $p->append(['discount_rate', 'effective_price']);
            return $p;
        });

        return response()->json($products);
    }

    /**
     * GET /api/products/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::active()
            ->where('slug', $slug)
            ->with([
                'category:id,name,slug',
                'seller:id,name,shop_name,shop_description',
                'reviews' => fn ($q) => $q->where('is_verified', true)->latest()->limit(5),
                'reviews.user:id,name',
            ])
            ->firstOrFail();

        $product->increment('view_count');
        $product->append(['discount_rate', 'effective_price']);

        return response()->json(['data' => $product]);
    }
}
