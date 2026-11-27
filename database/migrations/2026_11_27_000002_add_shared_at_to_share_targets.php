<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Funnel step 4: the merchant shared this target (Web Share / copy). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_targets', function (Blueprint $table) {
            $table->timestamp('shared_at')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('share_targets', function (Blueprint $table) {
            $table->dropColumn('shared_at');
        });
    }
};
