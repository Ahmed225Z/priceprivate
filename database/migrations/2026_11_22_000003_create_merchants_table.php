<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §15.1 (merchants)
 * - slug بيتغيّر مرة واحدة خلال أول 7 أيام فقط (§8.2و) عشان الملصقات المطبوعة.
 * - default_qr_target_* = «اللينك اللي التاجر حاططه في الكود» (§6.3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('store_name');
            $table->string('slug')->unique();
            $table->timestamp('slug_changed_at')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('brand_color', 7)->nullable();  // الخطة المدفوعة فقط

            $table->string('governorate')->nullable()->index();
            $table->string('business_type')->nullable()->index();

            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('address')->nullable();
            $table->json('working_hours')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['active', 'suspended', 'deleted'])
                ->default('active')
                ->index();

            // الهدف الافتراضي للكود الرئيسي (§6.3)
            $table->enum('default_qr_target_type', ['store', 'category', 'product'])
                ->default('store');
            $table->unsignedBigInteger('default_qr_target_id')->nullable();

            $table->timestamp('prices_updated_at')->nullable();  // آخر تعديل لأي سعر
            $table->unsignedInteger('products_count')->default(0);
            $table->unsignedInteger('cache_version')->default(1); // لإبطال الكاش (§17)

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
