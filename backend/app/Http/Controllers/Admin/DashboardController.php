<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $todayOrders   = Order::whereDate('created_at', $today)->count();
        $todayRevenue  = Order::whereDate('created_at', $today)
            ->whereIn('status', ['paid', 'shipping', 'delivered'])
            ->sum('paid_amount');
        $totalMembers  = User::where('role', '!=', 'admin')->count();
        $totalProducts = Product::count();

        $recentOrders = Order::with('user')
            ->latest()
            ->limit(5)
            ->get();

        $recentMembers = User::where('role', '!=', 'admin')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'todayOrders', 'todayRevenue', 'totalMembers', 'totalProducts',
            'recentOrders', 'recentMembers'
        ));
    }
}
