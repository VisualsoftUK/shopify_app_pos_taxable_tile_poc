<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantle_feature_usage', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('mantle_plan_id')->constrained('mantle_plans')->onDelete('cascade');
            $table->foreignId('mantle_feature_id')->constrained('mantle_features')->onDelete('cascade');
            $table->integer('month_to_date_value')->default(0);
            $table->integer('current_value')->default(0);
            $table->timestamps();
            $table->primary(['user_id', 'mantle_plan_id', 'mantle_feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantle_feature_usage');
    }
};
