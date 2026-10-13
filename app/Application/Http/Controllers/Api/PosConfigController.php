<?php

namespace App\Application\Http\Controllers\Api;

use App\Application\Http\Controllers\Controller;
use App\Domain\Actions\Settings\GetDefaultOrderTypeAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Configuration for the POS Eat In / Takeaway tile (authenticated with the POS session token).
 */
class PosConfigController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'defaultOrderType' => GetDefaultOrderTypeAction::for($request->user())->get()->value,
        ]);
    }
}
