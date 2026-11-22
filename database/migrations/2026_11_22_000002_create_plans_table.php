<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §10.1, §15.1 (plans)
 * السعر وحد المنتجات قابلين للتعديل من لوحة الإدارة بدون نشر كود (§12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();              // free | pro
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);   // ممنوع float (§15.2)
            $table->string('currency', 3)->default('EGP');
            $table->unsignedSmallInteger('duration_days')->default(365);
            $table->unsignedInteger('product_limit')->nullable(); // null = غير محدود
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
