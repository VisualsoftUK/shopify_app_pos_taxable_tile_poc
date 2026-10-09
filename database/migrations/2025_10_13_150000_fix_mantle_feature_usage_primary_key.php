<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create a temporary table with the new structure
        Schema::create('mantle_feature_usage_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('mantle_plan_id')->constrained('mantle_plans')->onDelete('cascade');
            $table->foreignId('mantle_feature_id')->constrained('mantle_features')->onDelete('cascade');
            $table->integer('month_to_date_value')->default(0);
            $table->integer('current_value')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'mantle_plan_id', 'mantle_feature_id'], 'feature_usage_unique');
        });

        // Drop the old table
        Schema::dropIfExists('mantle_feature_usage');

        // Rename the new table to the original name
        Schema::rename('mantle_feature_usage_temp', 'mantle_feature_usage');
    }

    public function down(): void
    {
        // Create a temporary table with the old structure
        Schema::create('mantle_feature_usage_temp', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('mantle_plan_id')->constrained('mantle_plans')->onDelete('cascade');
            $table->foreignId('mantle_feature_id')->constrained('mantle_features')->onDelete('cascade');
            $table->integer('month_to_date_value')->default(0);
            $table->integer('current_value')->default(0);
            $table->timestamps();
            $table->primary(['user_id', 'mantle_plan_id', 'mantle_feature_id']);
        });

        // Copy data from new table to old table
        DB::table('mantle_feature_usage_temp')->insert(
            DB::table('mantle_feature_usage')->select([
                'user_id',
                'mantle_plan_id',
                'mantle_feature_id',
                'month_to_date_value',
                'current_value',
                'created_at',
                'updated_at',
            ])->get()->toArray()
        );

        // Drop the new table
        Schema::dropIfExists('mantle_feature_usage');

        // Rename temp table back to original name
        Schema::rename('mantle_feature_usage_temp', 'mantle_feature_usage');
    }
};
