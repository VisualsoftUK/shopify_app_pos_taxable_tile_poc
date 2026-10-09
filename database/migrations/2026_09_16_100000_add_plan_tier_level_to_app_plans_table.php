<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_plans', function (Blueprint $table) {
            $table->unsignedInteger('plan_tier_level')->nullable()->after('trial_days');
        });
    }

    public function down(): void
    {
        Schema::table('app_plans', function (Blueprint $table) {
            $table->dropColumn('plan_tier_level');
        });
    }
};
