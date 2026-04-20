<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * 트리 구조로 반환. ?flat=1 이면 평탄 목록.
     */
    public function index(): JsonResponse
    {
        $flat = request()->boolean('flat');

        if ($flat) {
            $categories = Category::active()
                ->orderBy('sort_order')
                ->get(['id', 'parent_id', 'name', 'slug', 'image', 'sort_order']);

            return response()->json(['data' => $categories]);
        }

        $roots = Category::active()
            ->roots()
            ->with(['children' => fn ($q) => $q->active()->with(['children' => fn ($q2) => $q2->active()])])
            ->orderBy('sort_order')
            ->get(['id', 'parent_id', 'name', 'slug', 'image', 'sort_order']);

        return response()->json(['data' => $roots]);
    }

    /**
     * GET /api/categories/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::active()
            ->where('slug', $slug)
            ->with(['children' => fn ($q) => $q->active(), 'parent'])
            ->firstOrFail();

        return response()->json(['data' => $category]);
    }
}
