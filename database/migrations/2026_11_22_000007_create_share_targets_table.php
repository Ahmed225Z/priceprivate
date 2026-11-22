<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §15.1 (share_targets)
 * سجل أكواد QR اللي التاجر ولّدها — للمتابعة ولإعادة الطباعة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['store', 'category', 'product']);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('label')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['merchant_id', 'type', 'target_id'], 'share_targets_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_targets');
    }
};
