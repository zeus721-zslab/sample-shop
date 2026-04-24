<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\NoticeController;
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
        Route::get('/stats',     [StatsController::class, 'index'])->name('stats');

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

        // 공지사항 관리
        Route::get('/notices',              [NoticeController::class, 'index'])->name('notices.index');
        Route::get('/notices/create',       [NoticeController::class, 'create'])->name('notices.create');
        Route::post('/notices',             [NoticeController::class, 'store'])->name('notices.store');
        Route::get('/notices/{notice}/edit', [NoticeController::class, 'edit'])->name('notices.edit');
        Route::put('/notices/{notice}',     [NoticeController::class, 'update'])->name('notices.update');
        Route::delete('/notices/{notice}',  [NoticeController::class, 'destroy'])->name('notices.destroy');

        // FAQ 관리
        Route::get('/faqs',              [FaqController::class, 'index'])->name('faqs.index');
        Route::get('/faqs/create',       [FaqController::class, 'create'])->name('faqs.create');
        Route::post('/faqs',             [FaqController::class, 'store'])->name('faqs.store');
        Route::get('/faqs/{faq}/edit',   [FaqController::class, 'edit'])->name('faqs.edit');
        Route::put('/faqs/{faq}',        [FaqController::class, 'update'])->name('faqs.update');
        Route::delete('/faqs/{faq}',     [FaqController::class, 'destroy'])->name('faqs.destroy');

        // 쿠폰 관리
        Route::get('/coupons',              [CouponController::class, 'index'])->name('coupons.index');
        Route::get('/coupons/create',       [CouponController::class, 'create'])->name('coupons.create');
        Route::post('/coupons',             [CouponController::class, 'store'])->name('coupons.store');
        Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
        Route::put('/coupons/{coupon}',     [CouponController::class, 'update'])->name('coupons.update');
        Route::delete('/coupons/{coupon}',  [CouponController::class, 'destroy'])->name('coupons.destroy');

        // 1:1 문의 관리
        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    });
});

// ── 1:1 문의 (별도 미들웨어 그룹 없이 zslab-manage prefix 내 추가) ──────────────
// 위 Route::prefix('zslab-manage') 블록 외부에서 직접 추가
