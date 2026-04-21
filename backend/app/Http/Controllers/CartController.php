<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    /** GET /api/cart */
    public function index(Request $request): JsonResponse
    {
        $data = $this->cart->all($request->user()->id);
        return response()->json($data);
    }

    /** POST /api/cart */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:99',
            'options'    => 'sometimes|array',
        ]);

        try {
            $item = $this->cart->add(
                $request->user()->id,
                $validated['product_id'],
                $validated['quantity'],
                $validated['options'] ?? [],
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($item, 201);
    }

    /** PATCH /api/cart/{cartItemId} */
    public function update(Request $request, string $cartItemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $ok = $this->cart->update($request->user()->id, $cartItemId, $validated['quantity']);

        if (! $ok) {
            return response()->json(['message' => '장바구니 아이템을 찾을 수 없습니다.'], 404);
        }

        return response()->json(['message' => '수량이 변경되었습니다.']);
    }

    /** DELETE /api/cart/{cartItemId} */
    public function destroy(Request $request, string $cartItemId): JsonResponse
    {
        $removed = $this->cart->remove($request->user()->id, $cartItemId);

        if (! $removed) {
            return response()->json(['message' => '장바구니 아이템을 찾을 수 없습니다.'], 404);
        }

        return response()->json(null, 204);
    }

    /** DELETE /api/cart */
    public function clear(Request $request): JsonResponse
    {
        $this->cart->clear($request->user()->id);
        return response()->json(null, 204);
    }
}
