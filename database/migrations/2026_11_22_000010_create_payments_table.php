<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §10.2, §15.1 (payments)
 *
 * 🔒 التفعيل بيحصل من Webhook موقّع فقط، مش من رجوع العميل.
 * 🔒 gateway_transaction_id فريد ← تكرار نفس الـ webhook ما يفعّلش اشتراكين (idempotency).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_id')->constrained();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('EGP');
            $table->enum('gateway', ['paymob', 'fawry', 'manual', 'mock']);

            $table->string('gateway_transaction_id')->nullable()->unique();
            $table->string('gateway_order_id')->nullable()->index();

            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])
                ->default('pending')
                ->index();

            $table->json('raw_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
