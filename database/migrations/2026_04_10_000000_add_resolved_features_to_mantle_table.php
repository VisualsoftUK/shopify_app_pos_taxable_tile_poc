<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mantle', function (Blueprint $table) {
            $table->json('resolved_features')->nullable()->after('billable_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantle', function (Blueprint $table) {
            $table->dropColumn('resolved_features');
        });
    }
};
