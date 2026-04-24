<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\MyController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// ── Health ─────────────────────────────────────────────────────────────────────

Route::get('/health', fn () => response()->json(['status' => 'ok', 'service' => 'zslab-api']));

// ── Auth ───────────────────────────────────────────────────────────────────────

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // 소셜 로그인 — 라우트만 설계, 실제 OAuth 연동 추후
    Route::get('/social/{provider}/redirect', [AuthController::class, 'socialRedirect']);
    Route::get('/social/{provider}/callback', [AuthController::class, 'socialCallback']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });
});

// ── Products & Categories (Public) ────────────────────────────────────────────

Route::get('/categories',        [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);

Route::get('/products',          [ProductController::class, 'index']);
Route::get('/products/{slug}',   [ProductController::class, 'show']);

// 상품 리뷰 (조회는 비로그인 가능)
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);

// ── 검색 ──────────────────────────────────────────────────────────────────────

Route::get('/search', [SearchController::class, 'index']);

// ── 공지사항 / FAQ ────────────────────────────────────────────────────────────

Route::get('/notices',       [NoticeController::class, 'index']);
Route::get('/notices/{notice}', [NoticeController::class, 'show']);
Route::get('/faqs',          [FaqController::class, 'index']);

// ── Authenticated ─────────────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Cart
    Route::get('/cart',                 [CartController::class, 'index']);
    Route::post('/cart',                [CartController::class, 'store']);
    Route::patch('/cart/{cartItemId}',  [CartController::class, 'update']);
    Route::delete('/cart/{cartItemId}', [CartController::class, 'destroy']);
    Route::delete('/cart',              [CartController::class, 'clear']);

    // Orders
    Route::get('/orders',                   [OrderController::class, 'index']);
    Route::post('/orders',                  [OrderController::class, 'store']);
    Route::get('/orders/{id}',              [OrderController::class, 'show']);
    Route::post('/orders/{id}/confirm',     [OrderController::class, 'confirm']);
    Route::post('/orders/{id}/cancel',      [OrderController::class, 'cancel']);
    Route::patch('/orders/{id}/status',     [OrderController::class, 'updateStatus']);

    // Wishlist
    Route::get('/wishlist',                         [WishlistController::class, 'index']);
    Route::post('/wishlist/{productId}',            [WishlistController::class, 'toggle']);
    Route::delete('/wishlist/{productId}',          [WishlistController::class, 'destroy']);
    Route::get('/wishlist/check/{productId}',       [WishlistController::class, 'check']);

    // Reviews (인증 필요)
    Route::post('/products/{productId}/reviews',    [ReviewController::class, 'store']);
    Route::delete('/reviews/{id}',                  [ReviewController::class, 'destroy']);

    // My
    Route::prefix('my')->group(function () {
        Route::get('/profile',   [MyController::class, 'profile']);
        Route::patch('/profile', [MyController::class, 'updateProfile']);
        Route::get('/orders',    [MyController::class, 'orders']);
        Route::get('/reviews',   [MyController::class, 'reviews']);
        Route::get('/wishlist',  [MyController::class, 'wishlist']);
    });
});

// ── Marketplace-only ──────────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'shop.mode:marketplace'])->group(function () {
    // Route::apiResource('/sellers', SellerController::class);
});
