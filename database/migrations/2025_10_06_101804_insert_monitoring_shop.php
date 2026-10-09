<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Visualsoft\Shopify\Enum\InternalFakeStore;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $test_store = DB::table('users')->where('email', '=', InternalFakeStore::STORE_EMAIL->value)->first();
        if (isset($test_store) === false) {
            DB::table('users')->insert(
                [
                    'name' => InternalFakeStore::STORE_NAME->value,
                    'email' => InternalFakeStore::STORE_EMAIL->value,
                    'password' => 'shpua_064897f3eb63d965d2fc54062e6a2c50',
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('email', '=', InternalFakeStore::STORE_EMAIL->value)->delete();
    }
};
