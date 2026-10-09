<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_pricing_usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_handle');
            $table->unsignedInteger('quantity');
            $table->string('idempotency_key')->unique();
            $table->timestamp('occurred_at');
            $table->timestamp('submitted_at')->nullable();
            $table->string('result_status')->default('pending');
            $table->text('result_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_pricing_usage_events');
    }
};
