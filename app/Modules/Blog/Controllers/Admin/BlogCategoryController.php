<?php

namespace App\Modules\Blog\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\BlogCategory;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->hasPermission('blog.categories.manage'), 403);

        return view('admin.blog.categories.index', [
            'categories' => BlogCategory::query()->withCount('posts')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('blog.categories.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'unique:blog_categories,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = BlogCategory::query()->create([
            ...$validated,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'created_by' => $request->user()->getKey(),
            'updated_by' => $request->user()->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: 'blog.category.created',
            action: 'create_blog_category',
            actor: $request->user(),
            auditable: $category,
            newValues: ['name' => $category->name, 'slug' => $category->slug],
        );

        return back()->with('status', 'blog-category-created');
    }

    public function update(Request $request, BlogCategory $category): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('blog.categories.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'unique:blog_categories,slug,'.$category->getKey()],
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
            eventType: 'blog.category.updated',
            action: 'update_blog_category',
            actor: $request->user(),
            auditable: $category,
            oldValues: $oldValues,
            newValues: ['name' => $category->name, 'slug' => $category->slug],
        );

        return back()->with('status', 'blog-category-updated');
    }
}
