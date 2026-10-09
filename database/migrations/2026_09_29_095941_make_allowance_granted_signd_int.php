<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_user_features', function (Blueprint $table) {
            $table->integer('allowance_granted')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('app_user_features', function (Blueprint $table) {
            $table->unsignedInteger('allowance_granted')->nullable()->change();
        });
    }
};
