<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantle_subscription_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mantle_id')->constrained('mantle');
            $table->dateTimeTz('current_period_start')->comment('currentPeriodStart sent by Mantle.');
            $table->dateTimeTz('current_period_end')->comment('currentPeriodEnd sent by Mantle.');
            $table->dateTimeTz('activated_at')->nullable()->comment('activatedAt sent by Mantle.');
            $table->dateTimeTz('cancel_on')->nullable()->comment('cancelOn sent by Mantle.');
            $table->dateTimeTz('cancelled_at')->nullable()->comment('cancelledAt sent by Mantle.');
            $table->dateTimeTz('frozen_at')->nullable()->comment('frozenAt sent by Mantle.');
            $table->dateTimeTz('created_at')->comment('createdAt sent by Mantle.');

            $table->softDeletes()->comment('The date and time the subscription period was mark as deleted by VS and the mantle.mantle_plan_id was reset.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantle_subscription_periods');
    }
};
