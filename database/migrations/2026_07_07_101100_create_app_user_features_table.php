<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_user_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('app_feature_id')->constrained('app_features')->cascadeOnDelete();
            $table->string('entitlement_type');
            $table->foreignId('app_addon_purchase_id')->nullable()->unique()->constrained('app_addon_purchases')->nullOnDelete();
            $table->foreignId('app_recurring_addon_subscription_id')->nullable()->constrained('app_recurring_addon_subscriptions')->nullOnDelete();
            $table->boolean('enabled')->nullable();
            $table->unsignedInteger('allowance_granted')->nullable();
            $table->unsignedInteger('allowance_used')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'app_feature_id', 'entitlement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_user_features');
    }
};
