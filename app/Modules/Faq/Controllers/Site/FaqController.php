<?php

namespace App\Modules\Faq\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Faq\Models\FaqCategory;
use App\Modules\Faq\Services\FaqCatalogService;
use App\Modules\Products\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __construct(
        private readonly FaqCatalogService $faqCatalogService,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->toString(),
            'category' => $request->string('category')->toString(),
            'product' => $request->string('product')->toString(),
        ];

        return view('faq.index', [
            'faqs' => $this->faqCatalogService->publicListing($filters),
            'categories' => FaqCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'products' => Product::query()->publicCatalog()->orderBy('name_current')->get(),
            'filters' => $filters,
            'seo' => [
                'meta_title' => 'FAQ | '.config('app.name', 'USNsoft'),
                'meta_description' => 'Answers about USNsoft services, products, workflows, and support.',
                'canonical_url' => route('faq.index'),
                'og_title' => 'FAQ | '.config('app.name', 'USNsoft'),
                'og_description' => 'Browse frequently asked questions about USNsoft products, services, and delivery workflows.',
            ],
        ]);
    }
}
