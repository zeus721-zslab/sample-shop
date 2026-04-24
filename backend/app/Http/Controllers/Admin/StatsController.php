<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function index(): View
    {
        // ── 1. 최근 30일 일별 매출 ──────────────────────────────────────
        $dailyRevenue = DB::select("
            SELECT
                DATE(created_at)      AS date,
                SUM(final_amount)     AS revenue,
                COUNT(*)              AS order_count
            FROM orders
            WHERE status != 'cancelled'
              AND created_at >= NOW() - INTERVAL 30 DAY
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");

        // ── 2. 주문 상태별 건수 ──────────────────────────────────────────
        $ordersByStatus = DB::select("
            SELECT status, COUNT(*) AS cnt
            FROM orders
            GROUP BY status
        ");

        // ── 3. 인기 상품 TOP 10 (주문 수량 기준) ────────────────────────
        $topProducts = DB::select("
            SELECT
                oi.product_name,
                SUM(oi.quantity)    AS total_qty,
                SUM(oi.total_price) AS total_revenue
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE o.status != 'cancelled'
            GROUP BY oi.product_name
            ORDER BY total_qty DESC
            LIMIT 10
        ");

        // ── 4. 시간대별 주문 분포 ────────────────────────────────────────
        $ordersByHour = DB::select("
            SELECT HOUR(created_at) AS hour, COUNT(*) AS cnt
            FROM orders
            GROUP BY HOUR(created_at)
            ORDER BY hour ASC
        ");

        // ── 5. 요약 KPI ─────────────────────────────────────────────────
        $kpi = DB::selectOne("
            SELECT
                COUNT(*)                                           AS total_orders,
                COALESCE(SUM(CASE WHEN status != 'cancelled' THEN final_amount END), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN final_amount END), 0) AS today_revenue,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) AS today_orders
            FROM orders
        ");

        $totalUsers = DB::selectOne("SELECT COUNT(*) AS cnt FROM users WHERE role NOT IN ('admin','demo')");

        return view('admin.stats.index', compact(
            'dailyRevenue',
            'ordersByStatus',
            'topProducts',
            'ordersByHour',
            'kpi',
            'totalUsers',
        ));
    }
}
