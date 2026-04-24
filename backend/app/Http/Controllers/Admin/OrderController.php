<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(private MembershipService $membership) {}

    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => ['required', 'in:pending,paid,shipping,delivered,cancelled']]);

        $newStatus = $request->status;
        $prevStatus = $order->status;

        // 타임스탬프 자동 설정
        $timestamps = match ($newStatus) {
            'shipping'  => ['shipped_at'   => now()],
            'delivered' => ['delivered_at' => now()],
            default     => [],
        };

        DB::transaction(function () use ($order, $newStatus, $timestamps) {
            $order->update(array_merge(['status' => $newStatus], $timestamps));

            // 배송 완료 시: 적립금 지급 + 등급 재산정
            if ($newStatus === 'delivered') {
                $user = $order->fresh()->user;
                $this->membership->earnPoints($user, $order->fresh());
                $this->membership->recalculateGrade($user);
            }
        });

        return back()->with('success', '주문 상태가 변경되었습니다.');
    }
}
