<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantle', function (Blueprint $table) {
            $table->timestamp('last_synced_from_mantle_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mantle', function (Blueprint $table) {
            $table->dropColumn('last_synced_from_mantle_at');
        });
    }
};
