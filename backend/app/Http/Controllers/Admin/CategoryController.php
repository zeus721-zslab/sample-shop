<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $parents    = Category::whereNull('parent_id')->with('children')->orderBy('sort_order')->get();
        $allParents = Category::whereNull('parent_id')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('parents', 'allParents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'parent_id'  => ['nullable', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        Category::create([
            'name'       => $data['name'],
            'slug'       => Str::slug($data['name']) . '-' . Str::random(4),
            'parent_id'  => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return back()->with('success', '카테고리가 추가되었습니다.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name'       => $data['name'],
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return back()->with('success', '카테고리가 수정되었습니다.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists() || $category->products()->exists()) {
            return back()->with('error', '하위 카테고리 또는 상품이 있어 삭제할 수 없습니다.');
        }
        $category->delete();
        return back()->with('success', '카테고리가 삭제되었습니다.');
    }
}
