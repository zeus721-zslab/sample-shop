<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

// ── 관리자 인증 ───────────────────────────────────────────────────────────────
Route::prefix('zslab-manage')->name('admin.')->group(function () {

    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('admin.auth');

    // ── 인증 필요 영역 ──────────────────────────────────────────────────────
    Route::middleware(['admin.auth', 'demo.guard'])->group(function () {

        Route::get('/',          fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // 상품 관리
        Route::get('/products',              [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create',       [ProductController::class, 'create'])->name('products.create');
        Route::post('/products',             [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}',    [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // 주문 관리
        Route::get('/orders',               [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}',       [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        // 회원 관리
        Route::get('/members',                    [MemberController::class, 'index'])->name('members.index');
        Route::patch('/members/{user}/grade',     [MemberController::class, 'updateGrade'])->name('members.grade');
        Route::patch('/members/{user}/toggle',    [MemberController::class, 'toggle'])->name('members.toggle');

        // 카테고리 관리
        Route::get('/categories',               [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories',              [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}',    [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });
});
