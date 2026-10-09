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
        Schema::table('mantle', function (Blueprint $table) {
            $table->string('api_token')->nullable()->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantle', function (Blueprint $table) {
            $table->dropUnique(['api_token']);
            $table->string('api_token')->nullable()->change();
        });
    }
};
