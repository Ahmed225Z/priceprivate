<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

/** Stand-in until the client picks Paymob or Fawry (SRS section 31 Q3). Signature: HMAC-SHA256 of the raw body. */
final class MockGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'mock';
    }

    public function createCheckout(Payment $payment): CheckoutSession
    {
        return new CheckoutSession(url('/payment/callback?order='.$payment->gateway_order_id), $payment->gateway_order_id);
    }

    public static function sign(string $body): string
    {
        return hash_hmac('sha256', $body, (string) config('services.payments.mock_secret'));
    }

    public function verifyWebhook(Request $request): bool
    {
        $given = (string) $request->header('X-Signature', '');

        return $given !== '' && hash_equals(self::sign($request->getContent()), $given);
    }

    public function parseWebhook(Request $request): WebhookPayload
    {
        $d = (array) json_decode($request->getContent(), true);

        return new WebhookPayload(
            (string) ($d['order_id'] ?? ''),
            (string) ($d['transaction_id'] ?? ''),
            ($d['status'] ?? '') === 'paid' ? 'paid' : 'failed',
            $d,
        );
    }
}
