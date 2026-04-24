<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipConfig;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function index()
    {
        $configs = MembershipConfig::orderBy('min_amount')->get();
        return view('admin.membership.index', compact('configs'));
    }

    public function update(Request $request, MembershipConfig $membershipConfig)
    {
        $validated = $request->validate([
            'min_amount'  => 'required|integer|min:0',
            'point_rate'  => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:200',
        ]);

        $membershipConfig->update($validated);

        return back()->with('success', "[{$membershipConfig->grade}] 등급 기준이 업데이트되었습니다.");
    }
}
