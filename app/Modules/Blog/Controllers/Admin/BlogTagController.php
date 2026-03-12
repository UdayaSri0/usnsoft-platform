<?php

namespace App\Modules\Blog\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\BlogTag;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogTagController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->hasPermission('blog.tags.manage'), 403);

        return view('admin.blog.tags.index', [
            'tags' => BlogTag::query()->withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('blog.tags.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'unique:blog_tags,slug'],
        ]);

        $tag = BlogTag::query()->create([
            ...$validated,
            'created_by' => $request->user()->getKey(),
            'updated_by' => $request->user()->getKey(),
        ]);

        $this->auditLogService->record(
            eventType: 'blog.tag.created',
            action: 'create_blog_tag',
            actor: $request->user(),
            auditable: $tag,
            newValues: ['name' => $tag->name, 'slug' => $tag->slug],
        );

        return back()->with('status', 'blog-tag-created');
    }

    public function update(Request $request, BlogTag $tag): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('blog.tags.manage'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'unique:blog_tags,slug,'.$tag->getKey()],
        ]);

        $oldValues = ['name' => $tag->name, 'slug' => $tag->slug];

        $tag->forceFill([
            ...$validated,
            'updated_by' => $request->user()->getKey(),
        ])->save();

        $this->auditLogService->record(
            eventType: 'blog.tag.updated',
            action: 'update_blog_tag',
            actor: $request->user(),
            auditable: $tag,
            oldValues: $oldValues,
            newValues: ['name' => $tag->name, 'slug' => $tag->slug],
        );

        return back()->with('status', 'blog-tag-updated');
    }
}
