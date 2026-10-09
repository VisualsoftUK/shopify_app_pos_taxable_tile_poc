<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_plans_to_features', function (Blueprint $table) {
            $table->foreignId('app_plan_id')->constrained('app_plans')->cascadeOnDelete();
            $table->foreignId('app_feature_id')->constrained('app_features')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->string('value')->nullable();
            $table->primary(['app_plan_id', 'app_feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_plans_to_features');
    }
};
