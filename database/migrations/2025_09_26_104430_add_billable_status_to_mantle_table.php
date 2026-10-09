<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantle', function (Blueprint $table) {
            $table->string('billable_status')->nullable()->comment('The customer\'s billable status from Mantle.');
        });
    }

    public function down(): void
    {
        Schema::table('mantle', function (Blueprint $table) {
            $table->dropColumn('billable_status');
        });
    }
};
