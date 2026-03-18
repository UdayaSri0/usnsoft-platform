<?php

namespace App\Modules\Comments\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Blog\Services\BlogCatalogService;
use App\Modules\Comments\Requests\CommentStoreRequest;
use App\Modules\Comments\Services\CommentService;
use Illuminate\Http\RedirectResponse;

class BlogCommentController extends Controller
{
    public function __construct(
        private readonly BlogCatalogService $blogCatalogService,
        private readonly CommentService $commentService,
    ) {}

    public function store(CommentStoreRequest $request, BlogPost $post): RedirectResponse
    {
        $resolved = $this->blogCatalogService->resolvePublicPost($post->slug);

        abort_unless($resolved, 404);

        $this->commentService->submit(
            commentable: $resolved,
            actor: $request->user(),
            payload: $request->validated(),
        );

        return redirect()
            ->route('blog.show', ['post' => $resolved->slug])
            ->with('status', 'blog-comment-submitted');
    }
}
