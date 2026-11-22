<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PROJECT_SPEC.md §15.1 (users) + §18
 * الدخول بـ OAuth فقط في v1 — مفيش كلمات مرور.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('email');
            $table->string('provider_id')->nullable()->after('provider');
            $table->string('avatar')->nullable()->after('provider_id');
            $table->enum('role', ['merchant', 'admin', 'moderator'])
                ->default('merchant')
                ->after('avatar')
                ->index();
            $table->timestamp('last_login_at')->nullable()->after('remember_token');

            $table->unique(['provider', 'provider_id']);
        });

        // الدخول بـ OAuth فقط: البريد وكلمة المرور ممكن يكونوا فاضيين.
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id', 'avatar', 'role', 'last_login_at']);
        });
    }
};
