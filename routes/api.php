<?php

use App\Application\Http\Controllers\Api\IndexController;
use App\Application\Http\Controllers\Api\PosConfigController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [IndexController::class, 'index']);

Route::middleware('auth.pos')->group(function () {
    Route::get('/pos/config', PosConfigController::class)->name('pos.config');
});
