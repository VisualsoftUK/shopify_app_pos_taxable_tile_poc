<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mantle_plans_to_features', function (Blueprint $table) {
            $table->foreignId('mantle_plan_id')->constrained('mantle_plans');
            $table->foreignId('mantle_feature_id')->constrained('mantle_features');
            $table->boolean('enabled')->default(true);
            $table->string('value')->nullable();
            $table->primary(['mantle_plan_id', 'mantle_feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantle_plans_to_features');
    }
};
