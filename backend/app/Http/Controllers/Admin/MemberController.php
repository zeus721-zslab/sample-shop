<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'admin')->where('role', '!=', 'demo');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($grade = $request->input('grade')) {
            $query->where('grade', $grade);
        }

        $members = $query->latest()->paginate(20)->withQueryString();
        return view('admin.members.index', compact('members'));
    }

    public function updateGrade(Request $request, User $user)
    {
        $request->validate(['grade' => ['required', 'in:newbie,silver,gold,vip']]);
        $user->update(['grade' => $request->grade]);
        return back()->with('success', '등급이 변경되었습니다.');
    }

    public function adjustPoints(Request $request, User $user)
    {
        $validated = $request->validate([
            'type'        => 'required|in:earn,use',
            'amount'      => 'required|integer|min:1',
            'description' => 'required|string|max:200',
        ]);

        try {
            DB::transaction(function () use ($user, $validated) {
                if ($validated['type'] === 'earn') {
                    $user->increment('points', $validated['amount']);
                } else {
                    if ($user->points < $validated['amount']) {
                        throw new \RuntimeException('적립금이 부족합니다.');
                    }
                    $user->decrement('points', $validated['amount']);
                }

                PointHistory::create([
                    'user_id'     => $user->id,
                    'type'        => $validated['type'],
                    'amount'      => $validated['amount'],
                    'description' => '[관리자] ' . $validated['description'],
                    'created_at'  => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', '적립금이 조정되었습니다.');
    }

    public function toggle(User $user)
    {
        $newActive = !$user->is_active;
        $user->update(['is_active' => $newActive]);

        // 계정 정지 시 기존 Sanctum 토큰 전체 폐기
        if (! $newActive) {
            $user->tokens()->delete();
        }

        $msg = $newActive ? '계정이 활성화되었습니다.' : '계정이 정지되었습니다.';
        return back()->with('success', $msg);
    }
}
