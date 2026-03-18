<?php

namespace App\Modules\Comments\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;
use App\Modules\Comments\Requests\CommentModerationRequest;
use App\Modules\Comments\Services\CommentService;
use App\Modules\Products\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Comment::class);

        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();
        $q = $request->string('q')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $comments = Comment::query()
            ->with(['user', 'moderator', 'commentable'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($type !== '', fn ($query) => $query->where('commentable_type', $type))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('submitted_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('submitted_at', '<=', $dateTo))
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($searchQuery) use ($q): void {
                    $searchQuery
                        ->where('body', 'like', '%'.$q.'%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', '%'.$q.'%')
                            ->orWhere('email', 'like', '%'.$q.'%'))
                        ->orWhereHasMorph('commentable', [BlogPost::class, Product::class], function ($commentableQuery, string $type) use ($q): void {
                            if ($type === BlogPost::class) {
                                $commentableQuery->where('title', 'like', '%'.$q.'%');

                                return;
                            }

                            if ($type === Product::class) {
                                $commentableQuery->where('name_current', 'like', '%'.$q.'%');
                            }
                        });
                });
            })
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'filters' => compact('status', 'type', 'q', 'dateFrom', 'dateTo'),
            'statuses' => CommentStatus::cases(),
            'types' => [
                'blog_post' => 'Blog Post',
                'product' => 'Product',
            ],
        ]);
    }

    public function moderate(CommentModerationRequest $request, Comment $comment): RedirectResponse
    {
        $status = CommentStatus::from($request->string('status')->toString());

        $this->authorize('moderateState', [$comment, $status]);

        $this->commentService->moderate(
            comment: $comment,
            status: $status,
            actor: $request->user(),
            reason: $request->string('moderation_reason')->toString() ?: null,
        );

        return back()->with('status', 'comment-moderated');
    }
}
