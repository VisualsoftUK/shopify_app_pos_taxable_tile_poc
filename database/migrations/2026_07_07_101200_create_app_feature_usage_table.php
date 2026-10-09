<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_feature_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('app_feature_id')->constrained('app_features')->cascadeOnDelete();
            $table->foreignId('app_subscription_period_id')->nullable()->constrained('app_subscription_periods')->nullOnDelete();
            $table->unsignedInteger('allowance_used')->default(0);
            $table->timestamp('billing_period_start')->nullable();
            $table->timestamp('billing_period_end')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'app_feature_id', 'app_subscription_period_id'], 'app_feature_usage_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_feature_usage');
    }
};
