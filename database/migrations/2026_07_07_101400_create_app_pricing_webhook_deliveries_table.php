<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_pricing_webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('event_key');
            $table->string('url');
            $table->json('payload');
            $table->string('status')->default('pending');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamps();

            $table->index(['event_key', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_pricing_webhook_deliveries');
    }
};
