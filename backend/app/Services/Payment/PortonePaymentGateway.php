<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * 포트원(구 아임포트) v2 결제 게이트웨이
 * PAYMENT_GATEWAY=portone 일 때 바인딩
 *
 * 필요 env:
 *   PORTONE_API_SECRET=
 *   PORTONE_CHANNEL_KEY=
 */
class PortonePaymentGateway implements PaymentGatewayInterface
{
    private string $apiSecret;
    private string $channelKey;
    private string $baseUrl = 'https://api.portone.io';

    public function __construct()
    {
        $this->apiSecret  = config('services.portone.api_secret', '');
        $this->channelKey = config('services.portone.channel_key', '');
    }

    public function prepare(array $payload): array
    {
        $paymentId = 'zslab_' . Str::uuid();

        // 포트원 v2 결제 예약 (클라이언트 SDK에서 사용할 payment_id 반환)
        return [
            'payment_id'  => $paymentId,
            'next_action' => 'sdk',
            'channel_key' => $this->channelKey,
            'order_name'  => "주문 {$payload['order_number']}",
            'amount'      => $payload['amount'],
            'currency'    => 'KRW',
            'buyer'       => [
                'name'  => $payload['buyer_name'],
                'email' => $payload['buyer_email'],
            ],
        ];
    }

    public function confirm(string $paymentId, int $expectedAmount): array
    {
        $response = Http::withToken($this->apiSecret)
            ->get("{$this->baseUrl}/payments/{$paymentId}");

        if ($response->failed()) {
            return ['success' => false, 'paid_amount' => 0, 'raw' => $response->json()];
        }

        $data       = $response->json();
        $status     = $data['status'] ?? '';
        $paidAmount = $data['amount']['total'] ?? 0;

        $success = $status === 'PAID' && $paidAmount === $expectedAmount;

        if ($success) {
            // 서버 측 최종 승인
            Http::withToken($this->apiSecret)
                ->post("{$this->baseUrl}/payments/{$paymentId}/confirm");
        }

        return [
            'success'     => $success,
            'paid_amount' => $paidAmount,
            'raw'         => $data,
        ];
    }

    public function refund(string $paymentId, int $amount, string $reason = ''): array
    {
        $response = Http::withToken($this->apiSecret)
            ->post("{$this->baseUrl}/payments/{$paymentId}/cancel", [
                'reason'        => $reason ?: '고객 요청 환불',
                'cancel_amount' => $amount,
            ]);

        $data = $response->json();

        return [
            'success'   => $response->successful(),
            'refund_id' => $data['cancellation']['pgTxId'] ?? '',
        ];
    }
}
