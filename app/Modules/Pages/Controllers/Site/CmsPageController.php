<?php

namespace App\Modules\Pages\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Services\PageRenderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function __construct(
        private readonly PageRenderService $pageRenderService,
    ) {}

    public function __invoke(Request $request, ?string $path = null): View
    {
        $resolvedPath = $path === null ? '/' : '/'.$path;
        $version = $this->pageRenderService->resolvePublishedVersion($resolvedPath);

        if (! $version) {
            if ($resolvedPath === '/') {
                return view('welcome');
            }

            abort(404);
        }

        return view('public.cms-page', [
            'page' => $version->page,
            'version' => $version,
            'blocks' => $this->pageRenderService->renderableBlocks($version),
            'seo' => is_array($version->seo_snapshot_json) ? $version->seo_snapshot_json : [],
            'isPreview' => false,
        ]);
    }
}
