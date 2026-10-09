<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_plans', function (Blueprint $table) {
            $table->dropColumn('shopify_app_gid');
        });
    }

    public function down(): void
    {
        Schema::table('app_plans', function (Blueprint $table) {
            $table->string('shopify_app_gid')->nullable()->after('trial_days');
        });
    }
};
