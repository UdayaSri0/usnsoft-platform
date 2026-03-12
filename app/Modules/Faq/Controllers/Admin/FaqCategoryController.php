<?php

namespace App\Modules\Faq\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Faq\Models\FaqCategory;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqCategoryController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->hasPermission('faq.categories.manage'), 403);

        return view('admin.faq.categories.index', [
            'categories' => FaqCategory::query()->withCount('faqs')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('faq.categories.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'unique:faq_categories,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = FaqCategory::query()->create([
            ...$validated,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'created_by' => $request->user()->getKey(),
            'updated_by' => $request->user()->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: 'faq.category.created',
            action: 'create_faq_category',
            actor: $request->user(),
            auditable: $category,
            newValues: ['name' => $category->name, 'slug' => $category->slug],
        );

        return back()->with('status', 'faq-category-created');
    }

    public function update(Request $request, FaqCategory $category): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('faq.categories.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'unique:faq_categories,slug,'.$category->getKey()],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldValues = ['name' => $category->name, 'slug' => $category->slug];

        $category->forceFill([
            ...$validated,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'updated_by' => $request->user()->getKey(),
        ])->save();

        $this->auditLogService->record(
            eventType: 'faq.category.updated',
            action: 'update_faq_category',
            actor: $request->user(),
            auditable: $category,
            oldValues: $oldValues,
            newValues: ['name' => $category->name, 'slug' => $category->slug],
        );

        return back()->with('status', 'faq-category-updated');
    }
}
