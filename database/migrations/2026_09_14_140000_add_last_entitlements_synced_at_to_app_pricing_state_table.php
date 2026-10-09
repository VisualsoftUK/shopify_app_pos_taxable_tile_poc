<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_pricing_state', function (Blueprint $table) {
            $table->timestamp('last_entitlements_synced_at')->nullable()->after('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('app_pricing_state', function (Blueprint $table) {
            $table->dropColumn('last_entitlements_synced_at');
        });
    }
};
