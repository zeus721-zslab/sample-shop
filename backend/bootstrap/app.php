<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'shop.mode'  => \App\Http\Middleware\ShopMode::class,
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'demo.guard' => \App\Http\Middleware\DemoGuard::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API 라우트에서 인증 실패 시 JSON 401 반환
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json(['message' => '인증이 필요합니다.'], 401);
            }
        });
    })->create();
