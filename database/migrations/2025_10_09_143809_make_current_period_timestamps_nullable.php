<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantle_subscription_periods', function (Blueprint $table) {
            $table->timestamp('current_period_start')->nullable()->change()->comment('currentPeriodStart sent by Mantle.');
            $table->timestamp('current_period_end')->nullable()->change()->comment('currentPeriodEnd sent by Mantle.');
            $table->timestamp('created_at')->nullable()->change()->comment('createdAt sent by Mantle.');
        });
    }

    public function down(): void
    {
        Schema::table('mantle_subscription_periods', function (Blueprint $table) {
            $table->timestamp('current_period_start')->nullable(false)->change();
            $table->timestamp('current_period_end')->nullable(false)->change();
            $table->timestamp('created_at')->nullable(false)->change();
        });
    }
};
