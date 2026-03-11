<?php

namespace App\Modules\Pages\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\PageVersion;
use App\Modules\Pages\Services\PageRenderService;
use App\Modules\Pages\Services\PreviewTokenService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class CmsPreviewController extends Controller
{
    public function __construct(
        private readonly PageRenderService $pageRenderService,
        private readonly PreviewTokenService $previewTokenService,
    ) {}

    public function __invoke(Request $request, PageVersion $version): Response
    {
        $user = $request->user();
        $canPreview = $user?->can('preview', $version) ?? false;

        if (! $canPreview) {
            $token = $request->query('token');

            abort_unless(is_string($token) && $this->previewTokenService->verify($version, $token, $user), 403);
        }

        return response()->view('public.cms-page', [
            'page' => $version->page,
            'version' => $version,
            'blocks' => $this->pageRenderService->renderableBlocks($version),
            'seo' => is_array($version->seo_snapshot_json) ? $version->seo_snapshot_json : [],
            'isPreview' => true,
        ])->header('Cache-Control', 'no-store, private');
    }
}
