<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Visualsoft\Shopify\Enum\MerchantBracket;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('auto_assigned_bracket', MerchantBracket::cases())->default(MerchantBracket::MIDDLE);
            $table->enum('manually_assigned_bracket', MerchantBracket::cases())->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['auto_assigned_bracket', 'manually_assigned_bracket']);
        });
    }
};
