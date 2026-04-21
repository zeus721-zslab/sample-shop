<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'admin');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($grade = $request->input('grade')) {
            $query->where('role', $grade);
        }

        $members = $query->latest()->paginate(20)->withQueryString();
        return view('admin.members.index', compact('members'));
    }

    public function updateGrade(Request $request, User $user)
    {
        $request->validate(['grade' => ['required', 'in:customer,silver,gold,vip']]);
        $user->update(['role' => $request->grade]);
        return back()->with('success', '등급이 변경되었습니다.');
    }

    public function toggle(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $msg = $user->is_active ? '계정이 활성화되었습니다.' : '계정이 정지되었습니다.';
        return back()->with('success', $msg);
    }
}
