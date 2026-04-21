<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'detail'      => ['nullable', 'string'],
            'price'       => ['required', 'integer', 'min:0'],
            'sale_price'  => ['nullable', 'integer', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'status'      => ['required', 'in:active,inactive,soldout'],
            'image'       => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        $images = [];
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $images[] = '/storage/' . $path;
        }

        Product::create([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']) . '-' . Str::random(5),
            'description' => $data['description'] ?? null,
            'detail'      => $data['detail'] ?? null,
            'price'       => $data['price'],
            'sale_price'  => $data['sale_price'] ?? null,
            'stock'       => $data['stock'],
            'status'      => $data['status'],
            'images'      => $images,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', '상품이 등록되었습니다.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'detail'      => ['nullable', 'string'],
            'price'       => ['required', 'integer', 'min:0'],
            'sale_price'  => ['nullable', 'integer', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'status'      => ['required', 'in:active,inactive,soldout'],
            'image'       => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        $images = $product->images ?? [];
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $images = ['/storage/' . $path];
        }

        $product->update([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'detail'      => $data['detail'] ?? null,
            'price'       => $data['price'],
            'sale_price'  => $data['sale_price'] ?? null,
            'stock'       => $data['stock'],
            'status'      => $data['status'],
            'images'      => $images,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', '상품이 수정되었습니다.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', '상품이 삭제되었습니다.');
    }
}
