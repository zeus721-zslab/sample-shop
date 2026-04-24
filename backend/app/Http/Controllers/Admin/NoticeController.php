<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $query = Notice::query();
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }
        $notices = $query->orderByDesc('is_pinned')->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('admin.notices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'content'   => ['required', 'string'],
            'category'  => ['required', 'in:general,event,policy,delivery,system'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        Notice::create([
            'title'     => $data['title'],
            'content'   => $data['content'],
            'category'  => $data['category'],
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return redirect()->route('admin.notices.index')->with('success', '공지사항이 등록되었습니다.');
    }

    public function edit(Notice $notice)
    {
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $data = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'content'   => ['required', 'string'],
            'category'  => ['required', 'in:general,event,policy,delivery,system'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $notice->update([
            'title'     => $data['title'],
            'content'   => $data['content'],
            'category'  => $data['category'],
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return redirect()->route('admin.notices.index')->with('success', '공지사항이 수정되었습니다.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return redirect()->route('admin.notices.index')->with('success', '공지사항이 삭제되었습니다.');
    }
}
