<?php

namespace App\Modules\Products\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductDownload;
use App\Modules\Products\Services\ProductDownloadService;
use Illuminate\Http\Request;

class ProductDownloadController extends Controller
{
    public function __construct(
        private readonly ProductDownloadService $downloadService,
    ) {}

    public function __invoke(Request $request, Product $product, ProductDownload $download)
    {
        return $this->downloadService->handle($request->user(), $product, $download);
    }
}
