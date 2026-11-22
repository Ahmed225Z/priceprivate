<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §6.9, §15.1 (scan_stats)
 *
 * عدّ مجمّع يوميًا — مش سجل لكل زيارة.
 * ⛔ ممنوع أي بيانات شخصية: لا IP كامل ولا كوكيز تتبّع (§18).
 * العرض للتاجر مؤجّل للمرحلة الثانية (§4.2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->enum('target_type', ['store', 'category', 'product']);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->date('date');
            $table->enum('source', ['qr', 'link', 'direct'])->default('direct');
            $table->unsignedInteger('count')->default(0);

            $table->unique(
                ['merchant_id', 'target_type', 'target_id', 'date', 'source'],
                'scan_stats_unique_bucket',
            );
            $table->index(['merchant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_stats');
    }
};
