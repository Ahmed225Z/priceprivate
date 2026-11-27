<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function name(): string;

    public function createCheckout(Payment $payment): CheckoutSession;

    /** Signature check. A wrong signature must be rejected with 403 and logged. */
    public function verifyWebhook(Request $request): bool;

    public function parseWebhook(Request $request): WebhookPayload;
}
