<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantle_subscription_periods', function (Blueprint $table) {
            // Change dateTimeTz columns to timestamp to handle ISO 8601 format
            $table->timestamp('current_period_start')->comment('currentPeriodStart sent by Mantle.')->change();
            $table->timestamp('current_period_end')->comment('currentPeriodEnd sent by Mantle.')->change();
            $table->timestamp('activated_at')->nullable()->comment('activatedAt sent by Mantle.')->change();
            $table->timestamp('cancel_on')->nullable()->comment('cancelOn sent by Mantle.')->change();
            $table->timestamp('cancelled_at')->nullable()->comment('cancelledAt sent by Mantle.')->change();
            $table->timestamp('frozen_at')->nullable()->comment('frozenAt sent by Mantle.')->change();
            $table->timestamp('created_at')->comment('createdAt sent by Mantle.')->change();
        });
    }

    public function down(): void
    {
        Schema::table('mantle_subscription_periods', function (Blueprint $table) {
            // Revert back to dateTimeTz columns
            $table->dateTimeTz('current_period_start')->comment('currentPeriodStart sent by Mantle.')->change();
            $table->dateTimeTz('current_period_end')->comment('currentPeriodEnd sent by Mantle.')->change();
            $table->dateTimeTz('activated_at')->nullable()->comment('activatedAt sent by Mantle.')->change();
            $table->dateTimeTz('cancel_on')->nullable()->comment('cancelOn sent by Mantle.')->change();
            $table->dateTimeTz('cancelled_at')->nullable()->comment('cancelledAt sent by Mantle.')->change();
            $table->dateTimeTz('frozen_at')->nullable()->comment('frozenAt sent by Mantle.')->change();
            $table->dateTimeTz('created_at')->comment('createdAt sent by Mantle.')->change();
        });
    }
};
