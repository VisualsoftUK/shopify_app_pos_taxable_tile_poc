<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantle_plans', function (Blueprint $table) {
            $table->id();
            $table->string('mantle_plan_id')->index();
            $table->string('plan_name', 255);
            $table->text('plan_description')->nullable();
            $table->float('amount')->nullable();
            $table->string('currency')->nullable();
            $table->boolean('public')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantle_plans');
    }
};
