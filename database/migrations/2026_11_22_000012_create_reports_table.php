<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §9.1, §12, §15.1 (reports), §18
 *
 * البلاغ بدون تسجيل دخول، برقم IP **مُجزّأ (hashed)** مش مخزّن صريح (§18).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->morphs('reportable');   // Merchant | Product
            $table->enum('reason', [
                'wrong_price',
                'prohibited',
                'spam',
                'copyright',
                'other',
            ]);
            $table->text('note')->nullable();
            $table->char('reporter_ip_hash', 64)->nullable()->index();

            $table->enum('status', ['new', 'reviewed', 'actioned', 'dismissed'])
                ->default('new')
                ->index();
            $table->text('action_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
