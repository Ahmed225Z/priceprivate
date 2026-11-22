<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §10.3, §15.1 (subscriptions)
 *
 * free → active (365 يوم) → grace (7 أيام) → free
 * 🚨 انتهاء الاشتراك لا يوقف القائمة العامة أبدًا (§10.3 / D8).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->enum('status', ['active', 'grace', 'expired', 'cancelled'])
                ->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'status']);
            $table->index(['status', 'ends_at']);   // لجوب التنبيهات اليومي
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
