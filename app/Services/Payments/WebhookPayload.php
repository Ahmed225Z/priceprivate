<?php

declare(strict_types=1);

namespace App\Services\Payments;

final readonly class WebhookPayload
{
    /** @param 'paid'|'failed' $status */
    public function __construct(
        public string $orderId,
        public string $transactionId,
        public string $status,
        public array $raw = [],
    ) {}
}
