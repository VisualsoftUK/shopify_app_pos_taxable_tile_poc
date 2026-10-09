<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use App\Domain\DataObjects\CustomSettingsData;
use JsonSerializable;
use Visualsoft\Shopify\Facades\CurrentAppInstallation;
use Visualsoft\Shopify\Http\Requests\Admin\CreateFilesRequest;
use Visualsoft\Shopify\Objects\Product;

class MetafieldsController extends Controller
{
    /**
     *  Get metafields using a template data transfer object
     */
    public function get(): JsonSerializable
    {
        return CurrentAppInstallation::importMetafieldsInto(CustomSettingsData::class);
    }

    /**
     *  Validate and submit changes to metafields using a template data transfer object
     */
    public function set(CustomSettingsData $data): void
    {
        CurrentAppInstallation::saveMetafields($data);
    }

    /**
     *  Create and assign files to the shop's media library
     */
    public function assignFiles(CreateFilesRequest $request): array
    {
        return [
            'results' => app(Product::class)->fileCreate($request->validated()),
        ];
    }
}
