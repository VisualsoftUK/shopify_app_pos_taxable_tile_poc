<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_subscription_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_pricing_state_id')->constrained('app_pricing_state')->cascadeOnDelete();
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('cancel_on')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('frozen_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_subscription_periods');
    }
};
