<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantle_plans_to_features', function (Blueprint $table) {
            $table->dropColumn(['month_to_date_value', 'current_value']);
        });
    }

    public function down(): void
    {
        Schema::table('mantle_plans_to_features', function (Blueprint $table) {
            $table->integer('month_to_date_value')->default(0)->after('value');
            $table->integer('current_value')->default(0)->after('month_to_date_value');
        });
    }
};
