<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'demo' && !$request->isMethod('GET')) {
            return redirect()->back()
                ->with('error', '데모 계정은 조회만 가능합니다.');
        }

        return $next($request);
    }
}
