<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_addon_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('addon_pack_id')->constrained('app_addon_pack_definitions')->cascadeOnDelete();
            $table->string('shopify_charge_gid')->nullable();
            $table->string('status');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('credits_applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_addon_purchases');
    }
};
