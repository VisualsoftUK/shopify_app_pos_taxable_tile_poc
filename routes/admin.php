<?php

use App\Application\Http\Controllers\Admin\DashboardController;
use App\Application\Http\Controllers\Admin\HowItWorksController;
use App\Application\Http\Controllers\Admin\InstallUrlController;
use App\Application\Http\Controllers\Admin\MetafieldsController;
use App\Application\Http\Controllers\Admin\SettingsController;
use App\Application\Http\Controllers\Admin\ThemeController;
use App\Application\Http\Controllers\Admin\UploadsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group.
|
*/

// Merchant admin requires a functional App Pricing plan (Mantle-active shops
// still pass while mantle_plan_fallback is on).
Route::middleware(['app-pricing.plan'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/how-it-works', [HowItWorksController::class, 'index'])->name('how-it-works');
    Route::get('/custom-settings', [SettingsController::class, 'index'])->name('custom-settings');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

    // metafields
    Route::get('/metafields', [MetafieldsController::class, 'get']);
    Route::post('/metafields', [MetafieldsController::class, 'set']);
    Route::post('/metafields/files', [MetafieldsController::class, 'assignFiles']);

    // uploads
    Route::post('/uploads/receive', [UploadsController::class, 'receive'])->name('uploads.receive');
    Route::get('/app-status', ThemeController::class)->name('app-status');
    Route::post('/uploads/stage', [UploadsController::class, 'stage']);
    Route::get('/install-url', InstallUrlController::class)->name('install-url');
});
