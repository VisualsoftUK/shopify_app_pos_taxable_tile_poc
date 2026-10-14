<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_pricing_state', function (Blueprint $table) {
            $table->foreignId('entitlement_app_plan_id')
                ->nullable()
                ->after('app_plan_id')
                ->constrained('app_plans')
                ->nullOnDelete();
            $table->timestamp('entitlement_period_start')->nullable()->after('current_period_end');
            $table->timestamp('entitlement_period_end')->nullable()->after('entitlement_period_start');
            $table->timestamp('entitlement_switches_at')->nullable()->after('entitlement_period_end');
            $table->timestamp('entitlement_usage_anchor_at')->nullable()->after('entitlement_switches_at');
        });
    }

    public function down(): void
    {
        Schema::table('app_pricing_state', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entitlement_app_plan_id');
            $table->dropColumn([
                'entitlement_period_start',
                'entitlement_period_end',
                'entitlement_switches_at',
                'entitlement_usage_anchor_at',
            ]);
        });
    }
};
