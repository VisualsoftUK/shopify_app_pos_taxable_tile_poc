<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_pricing_meters', function (Blueprint $table) {
            $table->id();
            $table->string('event_handle')->unique();
            $table->string('linked_feature_key')->nullable();
            $table->string('tiers_mode')->nullable();
            $table->json('tiers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_pricing_meters');
    }
};
