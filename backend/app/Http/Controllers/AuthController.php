<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** POST /api/auth/register */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone'    => 'sometimes|string|max:20',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone'    => $validated['phone'] ?? null,
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    /** POST /api/auth/login */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['이메일 또는 비밀번호가 올바르지 않습니다.'],
            ]);
        }

        // 기존 토큰 정리 (선택적 — 멀티 디바이스 허용 시 제거)
        // $user->tokens()->delete();

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /** POST /api/auth/logout */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => '로그아웃 되었습니다.']);
    }

    /** GET /api/auth/me */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * GET /api/auth/social/{provider}/redirect
     * 소셜 로그인 리다이렉트 — 추후 Socialite 연동 예정
     */
    public function socialRedirect(string $provider): JsonResponse
    {
        $supported = ['kakao', 'naver', 'google'];

        if (! in_array($provider, $supported)) {
            return response()->json(['message' => "지원하지 않는 소셜 로그인입니다: {$provider}"], 422);
        }

        // TODO: Socialite::driver($provider)->stateless()->redirect()->getTargetUrl()
        return response()->json([
            'message'  => '소셜 로그인 준비 중입니다. 추후 지원 예정입니다.',
            'provider' => $provider,
        ], 501);
    }

    /**
     * GET /api/auth/social/{provider}/callback
     * 소셜 로그인 콜백 — 추후 Socialite 연동 예정
     */
    public function socialCallback(string $provider): JsonResponse
    {
        // TODO: Socialite 연동 후 구현
        // $socialUser = Socialite::driver($provider)->stateless()->user();
        // $user = User::firstOrCreate(['social_provider' => $provider, 'social_id' => $socialUser->getId()], [...]);
        // return response()->json(['user' => $user, 'token' => $user->createToken('auth')->plainTextToken]);

        return response()->json([
            'message'  => '소셜 로그인 준비 중입니다.',
            'provider' => $provider,
        ], 501);
    }
}
