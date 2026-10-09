<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantle_usage_events_submit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usage_event_log_id')->constrained('mantle_usage_events_log')->index();
            $table->tinyInteger('http_status_code')->nullable()->index();
            $table->text('response_body')->nullable();
            $table->timestamps(); // created_at is when we'd submit the usage event, updated_at is when we'd receive the response.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantle_usage_events_submit_log');
    }
};
