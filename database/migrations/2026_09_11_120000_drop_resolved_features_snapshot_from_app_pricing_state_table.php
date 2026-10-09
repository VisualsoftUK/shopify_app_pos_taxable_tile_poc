<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_pricing_state', function (Blueprint $table) {
            $table->dropColumn('resolved_features_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('app_pricing_state', function (Blueprint $table) {
            $table->json('resolved_features_snapshot')->nullable()->after('billable_status');
        });
    }
};
