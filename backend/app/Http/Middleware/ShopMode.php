<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopMode
{
    /**
     * single 모드에서 marketplace 전용 라우트를 차단.
     *
     * 라우트에 middleware('shop.mode:marketplace') 를 붙이면
     * SHOP_MODE=single 환경에서 404 반환.
     */
    public function handle(Request $request, Closure $next, string $required = 'marketplace'): Response
    {
        $current = strtolower(env('SHOP_MODE', 'single'));

        if ($required === 'marketplace' && $current !== 'marketplace') {
            return response()->json(['message' => 'This feature is not available in single-store mode.'], 404);
        }

        return $next($request);
    }
}
