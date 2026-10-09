<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use Visualsoft\Shopify\Http\Controllers\Concerns\ShopifyFileUpload;
use Visualsoft\Shopify\Http\Requests\Admin\StagedFilesRequest;
use Visualsoft\Shopify\Http\Requests\Admin\UploadFileRequest;

class UploadsController extends Controller
{
    use ShopifyFileUpload;

    /**
     *  Stage upload
     */
    public function stage(StagedFilesRequest $request): array
    {
        return $this->stageFilesToShopify($request);
    }

    /**
     *  Recieve upload (testing only)
     */
    public function receive(UploadFileRequest $request): void
    {
        $this->uploadFileToLocal($request);
    }
}
