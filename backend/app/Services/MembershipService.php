<?php

namespace App\Services;

use App\Models\MembershipConfig;
use App\Models\Order;
use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    /**
     * 최근 12개월 구매 확정(delivered) 금액 기준으로 등급 재산정 후 저장
     */
    public function recalculateGrade(User $user): string
    {
        $totalAmount = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->where('delivered_at', '>=', now()->subYear())
            ->sum('paid_amount');

        $configs = MembershipConfig::orderByDesc('min_amount')->get();

        $newGrade = 'newbie';
        foreach ($configs as $config) {
            if ($totalAmount >= $config->min_amount) {
                $newGrade = $config->grade;
                break;
            }
        }

        $user->update(['grade' => $newGrade]);

        return $newGrade;
    }

    /**
     * 주문 확정(delivered) 시 등급별 적립금 지급
     */
    public function earnPoints(User $user, Order $order): int
    {
        $config = MembershipConfig::where('grade', $user->grade)->first();

        if (! $config || $config->point_rate <= 0) {
            return 0;
        }

        $earned = (int) floor($order->paid_amount * $config->point_rate / 100);

        if ($earned <= 0) {
            return 0;
        }

        DB::transaction(function () use ($user, $order, $earned, $config) {
            $user->increment('points', $earned);

            PointHistory::create([
                'user_id'     => $user->id,
                'order_id'    => $order->id,
                'type'        => 'earn',
                'amount'      => $earned,
                'description' => "[{$config->grade}] 주문 #{$order->order_number} 적립 ({$config->point_rate}%)",
                'created_at'  => now(),
            ]);

            $order->update(['earned_points' => $earned]);
        });

        return $earned;
    }

    /**
     * 적립금 사용 처리 (주문 생성 시)
     */
    public function usePoints(User $user, Order $order, int $usePoints): void
    {
        if ($usePoints <= 0) {
            return;
        }

        if ($user->points < $usePoints) {
            throw new \RuntimeException('적립금이 부족합니다.');
        }

        DB::transaction(function () use ($user, $order, $usePoints) {
            $user->decrement('points', $usePoints);

            PointHistory::create([
                'user_id'     => $user->id,
                'order_id'    => $order->id,
                'type'        => 'use',
                'amount'      => $usePoints,
                'description' => "주문 #{$order->order_number} 적립금 사용",
                'created_at'  => now(),
            ]);

            $order->update(['used_points' => $usePoints]);
        });
    }

    /**
     * 주문 취소 시 적립금 환원
     */
    public function refundPoints(User $user, Order $order): void
    {
        // 사용 포인트 환원
        if ($order->used_points > 0) {
            $user->increment('points', $order->used_points);

            PointHistory::create([
                'user_id'     => $user->id,
                'order_id'    => $order->id,
                'type'        => 'earn',
                'amount'      => $order->used_points,
                'description' => "주문 #{$order->order_number} 취소 - 적립금 환원",
                'created_at'  => now(),
            ]);
        }

        // 적립된 포인트 회수
        if ($order->earned_points > 0 && $user->points >= $order->earned_points) {
            $user->decrement('points', $order->earned_points);

            PointHistory::create([
                'user_id'     => $user->id,
                'order_id'    => $order->id,
                'type'        => 'use',
                'amount'      => $order->earned_points,
                'description' => "주문 #{$order->order_number} 취소 - 적립금 회수",
                'created_at'  => now(),
            ]);

            $order->update(['earned_points' => 0]);
        }
    }
}
