<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Visualsoft\Mantle\Enums\UsageEventType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantle_usage_events_log', function (Blueprint $table) {
            $table->string('type')->default(UsageEventType::EVENT->value)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('mantle_usage_events_log', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
