<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_feature_usage', function (Blueprint $table) {
            $table->unsignedInteger('usage_brought_forward')->default(0)->after('allowance_used');
            $table->foreignId('brought_forward_period_id')
                ->nullable()
                ->after('usage_brought_forward')
                ->constrained('app_subscription_periods')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('app_feature_usage', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brought_forward_period_id');
            $table->dropColumn('usage_brought_forward');
        });
    }
};
