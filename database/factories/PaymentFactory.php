<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'plan_id' => Plan::factory(),
            'amount' => 200,
            'currency' => 'EGP',
            'gateway' => 'mock',
            'gateway_order_id' => (string) Str::uuid(),
            'status' => Payment::STATUS_PENDING,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_PAID,
            'gateway_transaction_id' => (string) Str::uuid(),
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => Payment::STATUS_FAILED]);
    }

    /** معاملة بمعرّف محدد — لاختبار الـ idempotency بتاع الـ webhook (§10.2). */
    public function withTransaction(string $transactionId): static
    {
        return $this->state(fn () => ['gateway_transaction_id' => $transactionId]);
    }
}
