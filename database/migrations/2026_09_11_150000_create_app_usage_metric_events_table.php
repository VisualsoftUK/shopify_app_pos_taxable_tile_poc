<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_usage_metric_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_name');
            $table->string('action')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->unsignedBigInteger('mantle_usage_event_log_id')->nullable()->unique();
            $table->timestamp('created_at')->nullable();
            $table->softDeletes();

            $table->index(['user_id', 'event_name', 'created_at'], 'app_usage_metric_events_user_event_created_index');
            $table->index(['event_name', 'created_at'], 'app_usage_metric_events_event_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_usage_metric_events');
    }
};
