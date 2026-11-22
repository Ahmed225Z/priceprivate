<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Invoice> */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory()->paid(),
            'number' => 'INV-'.now()->format('Y').'-'.fake()->unique()->numerify('######'),
            'issued_at' => now(),
        ];
    }
}
