<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Notice::query();

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $notices = $query->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($notices);
    }

    public function show(Notice $notice): JsonResponse
    {
        return response()->json(['data' => $notice]);
    }
}
