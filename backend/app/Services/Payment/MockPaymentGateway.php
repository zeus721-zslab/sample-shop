<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

/**
 * 개발/테스트용 결제 게이트웨이 (실제 결제 없음)
 * PAYMENT_GATEWAY=mock 일 때 바인딩
 */
class MockPaymentGateway implements PaymentGatewayInterface
{
    public function prepare(array $payload): array
    {
        $paymentId = 'mock_' . Str::uuid();

        return [
            'payment_id'   => $paymentId,
            'next_action'  => 'none',   // Mock: 즉시 완료, 리다이렉트 없음
            'redirect_url' => null,
        ];
    }

    public function confirm(string $paymentId, int $expectedAmount): array
    {
        // Mock: 항상 성공, 금액 일치
        return [
            'success'     => true,
            'paid_amount' => $expectedAmount,
            'raw'         => ['gateway' => 'mock', 'payment_id' => $paymentId],
        ];
    }

    public function refund(string $paymentId, int $amount, string $reason = ''): array
    {
        return [
            'success'   => true,
            'refund_id' => 'refund_mock_' . Str::uuid(),
        ];
    }
}
