<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §15.1 (categories)
 * حذف قسم → منتجاته تنتقل لـ «غير مصنّف» ولا تتحذف (§15.2) — يُفرض في الـ Service.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['merchant_id', 'slug']);
            $table->index(['merchant_id', 'is_visible', 'sort_order'], 'categories_listing_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
