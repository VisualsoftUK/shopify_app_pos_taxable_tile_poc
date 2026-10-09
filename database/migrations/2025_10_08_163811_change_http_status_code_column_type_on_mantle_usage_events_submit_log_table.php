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
        Schema::table('mantle_usage_events_submit_log', function (Blueprint $table) {
            $table->unsignedInteger('http_status_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantle_usage_events_submit_log', function (Blueprint $table) {
            $table->tinyInteger('http_status_code')->nullable()->change();
        });
    }
};
