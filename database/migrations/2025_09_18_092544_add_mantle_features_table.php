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
        Schema::create('mantle_features', function (Blueprint $table) {
            $table->id();
            $table->string('mantle_feature_id')->index();
            $table->string('feature_name', 255);
            $table->string('feature_key', 255);
            $table->string('type', 255);
            $table->text('description')->nullable();
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantle_plans');
    }
};
