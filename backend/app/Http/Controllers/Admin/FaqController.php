<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query();
        if ($search = $request->input('search')) {
            $query->where('question', 'like', "%{$search}%");
        }
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }
        $faqs = $query->orderBy('category')->orderBy('sort_order')->paginate(20)->withQueryString();
        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question'   => ['required', 'string', 'max:500'],
            'answer'     => ['required', 'string'],
            'category'   => ['required', 'in:주문/결제,배송,반품/교환,회원/계정'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Faq::create([
            'question'   => $data['question'],
            'answer'     => $data['answer'],
            'category'   => $data['category'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ가 등록되었습니다.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'question'   => ['required', 'string', 'max:500'],
            'answer'     => ['required', 'string'],
            'category'   => ['required', 'in:주문/결제,배송,반품/교환,회원/계정'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $faq->update([
            'question'   => $data['question'],
            'answer'     => $data['answer'],
            'category'   => $data['category'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ가 수정되었습니다.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return redirect()->route('admin.faqs.index')->with('success', 'FAQ가 삭제되었습니다.');
    }
}
