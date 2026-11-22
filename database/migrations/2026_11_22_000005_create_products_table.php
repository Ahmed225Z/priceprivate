<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §15.1 (products), §6.2
 *
 * 🔒 public_code: 6 خانات Base32، فريد على مستوى المنصة كلها، و**ثابت للأبد**.
 *    الملصق المطبوع في المحل لازم يفضل شغال حتى لو اتغيّر اسم المنتج أو سعره أو قسمه.
 *    المنع متطبّق كمان على مستوى الموديل (Product::updating).
 *
 * الحذف soft delete عشان التوجيه الذكي يعرف يعمل fallback (§6.5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->char('public_code', 6)->unique();

            $table->string('image_path')->nullable();
            $table->string('short_description', 140)->nullable();

            $table->decimal('price', 12, 2)->nullable();   // ممنوع float (§15.2)
            $table->string('currency', 3)->default('EGP');
            $table->string('unit', 20)->nullable();        // قطعة / كيلو / متر / لتر / علبة

            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('price_updated_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['merchant_id', 'is_visible', 'sort_order'], 'products_listing_index');
            $table->index(['category_id', 'is_visible', 'sort_order'], 'products_category_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
