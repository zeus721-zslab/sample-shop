<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private SearchService $search) {}

    /** GET /api/search?q=키워드&page=1&per_page=20 */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q'        => 'required|string|min:1|max:100',
            'page'     => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
        ]);

        $result = $this->search->search(
            query:   $request->string('q')->toString(),
            page:    (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 20),
        );

        return response()->json($result);
    }

    /** GET /api/search/suggest?q=키워드 */
    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1|max:50',
        ]);

        $suggestions = $this->search->suggest(
            query: $request->string('q')->toString(),
            size:  5,
        );

        return response()->json(['suggestions' => $suggestions]);
    }
}
