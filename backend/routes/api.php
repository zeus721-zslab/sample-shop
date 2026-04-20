<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────

Route::get('/health', fn () => response()->json(['status' => 'ok', 'service' => 'zslab-api']));

Route::get('/categories',       [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);

Route::get('/products',         [ProductController::class, 'index']);
Route::get('/products/{slug}',  [ProductController::class, 'show']);

// ── Authenticated ─────────────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
});

// ── Marketplace-only (SHOP_MODE=marketplace) ──────────────────────────────────

Route::middleware('shop.mode:marketplace')->group(function () {
    // 셀러 관련 라우트는 marketplace 모드에서만 활성화
    // Route::apiResource('/sellers', SellerController::class);
});
