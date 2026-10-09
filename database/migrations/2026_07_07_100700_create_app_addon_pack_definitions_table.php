<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_addon_pack_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('pack_key')->unique();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3);
            $table->string('feature_key');
            $table->unsignedInteger('credit_amount');
            $table->unsignedInteger('expires_after_days')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_addon_pack_definitions');
    }
};
