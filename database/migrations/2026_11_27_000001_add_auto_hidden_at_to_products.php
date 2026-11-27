<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Marks products hidden by the plan limit (not by the merchant), so renewal can bring back only those (SRS D8).
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('auto_hidden_at')->nullable()->after('is_visible');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('auto_hidden_at');
        });
    }
};
