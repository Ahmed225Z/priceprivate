<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §12, §15.1 (settings)
 * إعدادات المنصة القابلة للتعديل بدون نشر كود:
 * حد المنتجات المجاني، تفعيل الإعلانات، نصوص الصفحات القانونية والـ FAQ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
