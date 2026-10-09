<?php

use App\Application\Http\Controllers\Web\IndexController;
use Illuminate\Support\Facades\Response as ResponseFacadeAlias;
use Illuminate\Support\Facades\Route;
use Osiset\ShopifyApp\Util;
use Symfony\Component\HttpFoundation\Response;

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

Route::post(
    '/webhook',
    function ($request) {
        return ResponseFacadeAlias::make('Invalid proxy signature.', Response::HTTP_UNAUTHORIZED);
    }
)
    ->middleware('auth.webhook')
    // This must be the final middleware!
    ->middleware('shopify_generic_webhooks')
    ->name(Util::getShopifyConfig('route_names.webhook') . '_generic');
