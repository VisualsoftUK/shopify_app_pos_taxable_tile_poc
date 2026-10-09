<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_features', function (Blueprint $table) {
            $table->string('allowance_policy')
                ->default('capped')
                ->after('default_value');
        });
    }

    public function down(): void
    {
        Schema::table('app_features', function (Blueprint $table) {
            $table->dropColumn('allowance_policy');
        });
    }
};
