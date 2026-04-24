<?php

namespace App\Http\Controllers;

use App\Models\MembershipConfig;
use App\Models\PointHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointController extends Controller
{
    /** GET /api/my/points */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $histories = PointHistory::where('user_id', $user->id)
            ->latest('created_at')
            ->take(50)
            ->get(['id', 'type', 'amount', 'description', 'order_id', 'created_at']);

        // 현재 등급 + 다음 등급 정보
        $configs = MembershipConfig::orderBy('min_amount')->get();

        $gradeInfo = [
            'current_grade' => $user->grade,
            'points'        => $user->points,
        ];

        // 다음 등급까지 남은 금액
        $gradeOrder = ['newbie', 'silver', 'gold', 'vip'];
        $currentIdx = array_search($user->grade, $gradeOrder);
        $nextIdx    = $currentIdx + 1;

        if ($nextIdx < count($gradeOrder)) {
            $nextGrade    = $gradeOrder[$nextIdx];
            $nextConfig   = $configs->firstWhere('grade', $nextGrade);
            $currentYear  = now()->subYear();

            // 최근 12개월 구매 확정 금액
            $purchasedAmount = $user->orders()
                ->where('status', 'delivered')
                ->where('delivered_at', '>=', $currentYear)
                ->sum('paid_amount');

            $gradeInfo['next_grade']          = $nextGrade;
            $gradeInfo['next_grade_amount']   = $nextConfig?->min_amount ?? 0;
            $gradeInfo['purchased_amount']    = $purchasedAmount;
            $gradeInfo['remaining_amount']    = max(0, ($nextConfig?->min_amount ?? 0) - $purchasedAmount);
            $gradeInfo['current_point_rate']  = $configs->firstWhere('grade', $user->grade)?->point_rate ?? 0;
        } else {
            $gradeInfo['next_grade']         = null;
            $gradeInfo['current_point_rate'] = $configs->firstWhere('grade', $user->grade)?->point_rate ?? 0;
        }

        return response()->json([
            'grade_info' => $gradeInfo,
            'histories'  => $histories,
        ]);
    }
}
