<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_pricing_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('app_plan_id')->nullable()->constrained('app_plans')->nullOnDelete();
            $table->string('status');
            $table->string('billing_period')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('cancel_at_end_of_cycle')->default(false);
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->string('shopify_legacy_subscription_gid')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_pricing_subscriptions');
    }
};
