<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::latest()->paginate(20);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateCoupon($request);

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', '쿠폰이 발행되었습니다.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $this->validateCoupon($request, $coupon->id);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', '쿠폰이 수정되었습니다.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', '쿠폰이 삭제되었습니다.');
    }

    private function validateCoupon(Request $request, ?int $ignoreId = null): array
    {
        $codeRule = 'required|string|max:50|unique:coupons,code';
        if ($ignoreId) {
            $codeRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'code'               => $codeRule,
            'name'               => 'required|string|max:100',
            'type'               => 'required|in:fixed,percent',
            'value'              => 'required|integer|min:1',
            'min_order_amount'   => 'nullable|integer|min:0',
            'max_discount_amount' => 'nullable|integer|min:0',
            'max_uses'           => 'nullable|integer|min:1',
            'is_active'          => 'boolean',
            'starts_at'          => 'nullable|date',
            'expires_at'         => 'nullable|date|after_or_equal:starts_at',
        ], [], [
            'code'               => '쿠폰 코드',
            'name'               => '쿠폰 이름',
            'type'               => '할인 유형',
            'value'              => '할인 값',
            'min_order_amount'   => '최소 주문금액',
            'max_discount_amount' => '최대 할인금액',
            'max_uses'           => '최대 사용횟수',
            'expires_at'         => '만료일',
        ]);
    }
}
