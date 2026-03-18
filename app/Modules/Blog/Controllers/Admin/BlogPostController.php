<?php

namespace App\Modules\Blog\Controllers\Admin;

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContentWorkflowActionRequest;
use App\Models\User;
use App\Modules\Blog\Models\BlogCategory;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Blog\Models\BlogTag;
use App\Modules\Blog\Requests\BlogPostStoreRequest;
use App\Modules\Blog\Requests\BlogPostUpdateRequest;
use App\Modules\Blog\Services\BlogPostService;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Media\Models\MediaAsset;
use App\Modules\Pages\Models\BlockDefinition;
use App\Modules\Pages\Models\ReusableBlock;
use App\Modules\Workflow\Notifications\StaffContentSubmittedForReviewNotification;
use App\Services\Content\DirectContentWorkflowService;
use App\Services\Notifications\OperationalNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function __construct(
        private readonly BlogPostService $blogPostService,
        private readonly DirectContentWorkflowService $workflowService,
        private readonly OperationalNotificationService $operationalNotificationService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BlogPost::class);

        $status = $request->string('status')->toString();
        $category = $request->string('category')->toString();
        $tag = $request->string('tag')->toString();
        $author = $request->string('author')->toString();
        $featured = $request->string('featured')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $q = $request->string('q')->toString();

        $posts = BlogPost::query()
            ->with(['category', 'tags', 'author'])
            ->withCount([
                'approvedComments as approved_comments_count',
                'comments as pending_comments_count' => fn ($query) => $query->where('status', CommentStatus::Pending->value),
            ])
            ->search($q)
            ->when($category !== '', fn ($query) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $category)))
            ->when($tag !== '', fn ($query) => $query->whereHas('tags', fn ($tagQuery) => $tagQuery->where('slug', $tag)))
            ->when($author !== '', fn ($query) => $query->where('author_user_id', $author))
            ->when($featured !== '', fn ($query) => $query->where('featured_flag', filter_var($featured, FILTER_VALIDATE_BOOL)))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($status !== '', fn ($query) => $query->where('workflow_state', $status))
            ->orderByDesc('featured_flag')
            ->orderByDesc('published_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog.index', [
            'posts' => $posts,
            'categories' => BlogCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'tags' => BlogTag::query()->orderBy('name')->get(),
            'authors' => User::query()->orderBy('name')->limit(200)->get(),
            'filters' => compact('status', 'category', 'tag', 'author', 'featured', 'dateFrom', 'dateTo', 'q'),
            'workflowStates' => ContentWorkflowState::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', BlogPost::class);

        return view('admin.blog.create', $this->editorViewData());
    }

    public function store(BlogPostStoreRequest $request): RedirectResponse
    {
        $post = $this->blogPostService->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.blog.edit', ['post' => $post->getKey()])
            ->with('status', 'blog-post-created');
    }

    public function edit(BlogPost $post): View
    {
        $this->authorize('view', $post);

        $post->load(['category', 'tags', 'author', 'featuredImage', 'relatedPosts', 'seoMeta.ogImage']);

        return view('admin.blog.edit', array_merge($this->editorViewData($post), [
            'post' => $post,
        ]));
    }

    public function update(BlogPostUpdateRequest $request, BlogPost $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $this->blogPostService->update($post, $request->user(), $request->validated());

        return redirect()
            ->route('admin.blog.edit', ['post' => $post->getKey()])
            ->with('status', 'blog-post-updated');
    }

    public function submitForReview(ContentWorkflowActionRequest $request, BlogPost $post): RedirectResponse
    {
        $this->authorize('submitForReview', $post);

        $this->workflowService->submitForReview($post, $request->user(), $request->string('notes')->toString() ?: null);

        $this->operationalNotificationService->notifyUsersWithPermission(
            'blog.approve',
            new StaffContentSubmittedForReviewNotification($post->fresh(), 'blog_post', $post->title),
        );

        $this->operationalNotificationService->dispatchBusinessEvent('blog.post.submitted_for_review', [
            'post_id' => $post->getKey(),
            'slug' => $post->slug,
        ]);

        return back()->with('status', 'blog-post-submitted');
    }

    public function approve(ContentWorkflowActionRequest $request, BlogPost $post): RedirectResponse
    {
        $this->authorize('approve', $post);

        $this->workflowService->approve($post, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'blog-post-approved');
    }

    public function reject(ContentWorkflowActionRequest $request, BlogPost $post): RedirectResponse
    {
        $this->authorize('reject', $post);

        $this->workflowService->reject($post, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'blog-post-rejected');
    }

    public function schedule(ContentWorkflowActionRequest $request, BlogPost $post): RedirectResponse
    {
        $this->authorize('schedule', $post);

        $publishAt = CarbonImmutable::parse($request->string('schedule_publish_at')->toString());
        $unpublishInput = $request->string('schedule_unpublish_at')->toString();
        $unpublishAt = $unpublishInput !== '' ? CarbonImmutable::parse($unpublishInput) : null;

        $this->workflowService->schedulePublish(
            content: $post,
            actor: $request->user(),
            publishAt: $publishAt,
            notes: $request->string('notes')->toString() ?: null,
            unpublishAt: $unpublishAt,
        );

        return back()->with('status', 'blog-post-scheduled');
    }

    public function publish(ContentWorkflowActionRequest $request, BlogPost $post): RedirectResponse
    {
        $this->authorize('publish', $post);

        if ($request->boolean('preview_confirmed')) {
            $this->workflowService->confirmPreview($post, $request->user());
        }

        $this->workflowService->publishNow(
            content: $post,
            actor: $request->user(),
            notes: $request->string('notes')->toString() ?: null,
            requirePreviewConfirmation: true,
        );

        return back()->with('status', 'blog-post-published');
    }

    public function archive(ContentWorkflowActionRequest $request, BlogPost $post): RedirectResponse
    {
        $this->authorize('archive', $post);

        $this->workflowService->archive($post, $request->user(), $request->string('notes')->toString() ?: null);

        return back()->with('status', 'blog-post-archived');
    }

    /**
     * @return array<string, mixed>
     */
    private function editorViewData(?BlogPost $post = null): array
    {
        return [
            'categories' => BlogCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'tags' => BlogTag::query()->orderBy('name')->get(),
            'authors' => User::query()->orderBy('name')->limit(200)->get(),
            'relatedPosts' => BlogPost::query()
                ->when($post, fn ($query) => $query->whereKeyNot($post->getKey()))
                ->orderByDesc('published_at')
                ->orderBy('title')
                ->get(),
            'mediaAssets' => MediaAsset::query()->orderByDesc('created_at')->limit(200)->get(),
            'definitions' => BlockDefinition::query()->where('is_active', true)->orderBy('category')->orderBy('name')->get(),
            'approvedReusableBlocks' => ReusableBlock::query()
                ->where('workflow_state', ContentWorkflowState::Published->value)
                ->where('approval_state', ApprovalState::Approved->value)
                ->orderBy('name')
                ->get(),
        ];
    }
}
