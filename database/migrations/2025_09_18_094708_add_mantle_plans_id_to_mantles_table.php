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
            $table->foreignId('mantle_plan_id')->nullable()->constrained('mantle_plans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantle', function (Blueprint $table) {
            $table->dropForeign(['mantle_plan_id']);
            $table->dropColumn('mantle_plan_id');
        });
    }
};
