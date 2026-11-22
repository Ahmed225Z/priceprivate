<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §11, §15.1 (ad_slots)
 *
 * 🚨 ممنوع منعًا باتًا أي إعلان فوق السعر أو يغطيه.
 * كل مكان له min_height محجوز مسبقًا لمنع القفز (CLS = 0).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_slots', function (Blueprint $table) {
            $table->id();
            $table->enum('code', [
                'list_after_first_category',
                'list_footer',
                'product_below_contact',
            ])->unique();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->enum('type', ['adsense', 'direct'])->default('adsense');

            $table->text('adsense_code')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('target_url')->nullable();

            $table->unsignedSmallInteger('min_height')->default(100); // حجز المساحة
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_slots');
    }
};
