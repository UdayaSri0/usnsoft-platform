<?php

namespace App\Modules\Products\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\ProductTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductTagController extends Controller
{
    public function index(): View
    {
        return view('admin.products.tags.index', [
            'tags' => ProductTag::query()
                ->withCount('versions')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:product_tags,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        ProductTag::query()->create([
            'name' => $validated['name'],
            'slug' => trim((string) ($validated['slug'] ?? '')) !== '' ? $validated['slug'] : Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'created_by' => $request->user()?->getKey(),
            'updated_by' => $request->user()?->getKey(),
        ]);

        return back()->with('status', 'product-tag-created');
    }

    public function update(Request $request, ProductTag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'unique:product_tags,slug,'.$tag->getKey()],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $tag->forceFill([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        return back()->with('status', 'product-tag-updated');
    }
}
