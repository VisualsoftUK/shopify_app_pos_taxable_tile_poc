<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_plans', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_plan_handle')->unique();
            $table->string('plan_name');
            $table->text('plan_description')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('billing_period')->nullable();
            $table->boolean('public')->default(true);
            $table->unsignedInteger('trial_days')->default(0);
            $table->string('shopify_app_gid')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_plans');
    }
};
