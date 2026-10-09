<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantle_subscription_periods', function (Blueprint $table) {
            $table->string('mantle_webhook_id')->nullable()->comment('The ID on webhooks related to the Plan and Customer.')->index();
        });
    }

    public function down(): void
    {
        Schema::table('mantle_subscription_periods', function (Blueprint $table) {
            $table->dropIndex(['mantle_webhook_id']);
            $table->dropColumn('mantle_webhook_id');
        });
    }
};
