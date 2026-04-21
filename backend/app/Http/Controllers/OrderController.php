<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /** GET /api/orders */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    /** GET /api/orders/{id} */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->firstOrFail();

        return response()->json($order);
    }

    /** POST /api/orders */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart_item_ids'             => 'sometimes|array',
            'cart_item_ids.*'           => 'string',
            'shipping_address'          => 'required|array',
            'shipping_address.recipient' => 'required|string|max:50',
            'shipping_address.phone'    => 'required|string|max:20',
            'shipping_address.address'  => 'required|string|max:200',
            'shipping_address.detail'   => 'sometimes|string|max:100',
            'shipping_address.postal_code' => 'required|string|max:10',
            'coupon_code'               => 'sometimes|string|max:50',
        ]);

        try {
            $result = $this->orderService->create(
                userId:          $request->user()->id,
                cartItemIds:     $validated['cart_item_ids'] ?? [],
                shippingAddress: $validated['shipping_address'],
                couponCode:      $validated['coupon_code'] ?? '',
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result, 201);
    }

    /** POST /api/orders/{id}/confirm */
    public function confirm(Request $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderService->confirm($id, $request->user()->id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => '주문을 찾을 수 없습니다.'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($order);
    }

    /** POST /api/orders/{id}/cancel */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderService->updateStatus($id, $request->user()->id, 'cancelled');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => '주문을 찾을 수 없습니다.'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($order);
    }

    /** PATCH /api/orders/{id}/status */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:shipping,delivered,cancelled',
        ]);

        try {
            $order = $this->orderService->updateStatus($id, $request->user()->id, $validated['status']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => '주문을 찾을 수 없습니다.'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($order);
    }
}
