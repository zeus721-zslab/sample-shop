<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Faq::query();

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $faqs = $query->orderBy('category')->orderBy('sort_order')->get();

        // 카테고리별 그룹핑
        $grouped = $faqs->groupBy('category')->map(fn ($items) => $items->values());

        return response()->json(['data' => $grouped, 'categories' => $grouped->keys()->values()]);
    }
}
