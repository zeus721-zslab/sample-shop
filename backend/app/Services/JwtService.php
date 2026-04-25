<?php

namespace App\Services;

class JwtService
{
    /**
     * HS256 JWT 생성
     */
    public function generate(array $payload, string $secret): string
    {
        $header  = $this->encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body    = $this->encode(json_encode($payload));
        $sig     = $this->encode(hash_hmac('sha256', "{$header}.{$body}", $secret, true));

        return "{$header}.{$body}.{$sig}";
    }

    /**
     * 사용자용 채팅 토큰 (7일)
     */
    public function userChatToken(string $userId, string $secret): string
    {
        return $this->generate([
            'userId'   => $userId,
            'userType' => 'user',
            'iat'      => time(),
            'exp'      => time() + 7 * 24 * 3600,
        ], $secret);
    }

    /**
     * 관리자용 채팅 토큰 (1시간)
     */
    public function adminChatToken(string $secret): string
    {
        return $this->generate([
            'userId'   => 'admin',
            'userType' => 'admin',
            'iat'      => time(),
            'exp'      => time() + 3600,
        ], $secret);
    }

    private function encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
