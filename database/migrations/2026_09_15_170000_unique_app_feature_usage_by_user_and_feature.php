<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('app_feature_usage')
            ->select('user_id', 'app_feature_id')
            ->groupBy('user_id', 'app_feature_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $rows = DB::table('app_feature_usage')
                ->where('user_id', $duplicate->user_id)
                ->where('app_feature_id', $duplicate->app_feature_id)
                ->orderByDesc('allowance_used')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get();

            $keepId = $rows->first()?->id;
            if ($keepId === null) {
                continue;
            }

            $deleteIds = $rows->pluck('id')->reject(fn ($id): bool => $id === $keepId)->all();
            if ($deleteIds !== []) {
                DB::table('app_feature_usage')->whereIn('id', $deleteIds)->delete();
            }
        }

        Schema::table('app_feature_usage', function (Blueprint $table) {
            $table->unique(['user_id', 'app_feature_id'], 'app_feature_usage_user_feature_unique');
        });

        Schema::table('app_feature_usage', function (Blueprint $table) {
            $table->dropUnique('app_feature_usage_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('app_feature_usage', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'app_feature_id', 'app_subscription_period_id'],
                'app_feature_usage_period_unique'
            );
        });

        Schema::table('app_feature_usage', function (Blueprint $table) {
            $table->dropUnique('app_feature_usage_user_feature_unique');
        });
    }
};
