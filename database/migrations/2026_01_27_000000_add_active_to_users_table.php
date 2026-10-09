<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('active')->default(true)->comment('If the store has been taken offline by Shopify. App data will be kept, but no further processing or allowance whilst inactive.');
        });

        // Mark any soft-deleted users as inactive
        DB::table('users')
            ->whereNotNull('deleted_at')
            ->update(['active' => false]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
