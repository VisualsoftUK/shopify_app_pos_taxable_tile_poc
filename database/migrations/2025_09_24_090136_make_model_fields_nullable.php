<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantle_usage_events_log', function (Blueprint $table) {
            $table->string('model_type')->nullable()->change();
            $table->string('model_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mantle_usage_events_log', function (Blueprint $table) {
            $table->string('model_type')->nullable(false)->change();
            $table->string('model_id')->nullable(false)->change();
        });
    }
};
