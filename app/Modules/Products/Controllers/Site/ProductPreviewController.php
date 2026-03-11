<?php

namespace App\Modules\Products\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\ProductVersion;
use App\Modules\Products\Services\ProductPreviewTokenService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductPreviewController extends Controller
{
    public function __construct(
        private readonly ProductController $productController,
        private readonly ProductPreviewTokenService $previewTokenService,
    ) {}

    public function __invoke(Request $request, ProductVersion $version): Response
    {
        $user = $request->user();
        $canPreview = $user?->can('preview', $version) ?? false;

        if (! $canPreview) {
            $token = $request->query('token');

            abort_unless(is_string($token) && $this->previewTokenService->verify($version, $token, $user), 403);
        }

        $view = $this->productController->renderPreview($request, $version, true);

        return response()
            ->view('products.show', $view->getData())
            ->header('Cache-Control', 'no-store, private');
    }
}
