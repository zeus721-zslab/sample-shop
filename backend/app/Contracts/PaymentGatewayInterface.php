<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * 결제 요청 초기화 — 결제창 URL 또는 클라이언트 토큰 반환
     *
     * @param  array{amount: int, order_number: string, buyer_name: string, buyer_email: string}  $payload
     * @return array{payment_id: string, next_action: string, redirect_url?: string}
     */
    public function prepare(array $payload): array;

    /**
     * 결제 완료 확인 / 금액 검증
     *
     * @return array{success: bool, paid_amount: int, raw: array}
     */
    public function confirm(string $paymentId, int $expectedAmount): array;

    /**
     * 환불 처리
     *
     * @return array{success: bool, refund_id: string}
     */
    public function refund(string $paymentId, int $amount, string $reason = ''): array;
}
