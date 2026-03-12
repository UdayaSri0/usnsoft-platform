<?php

namespace App\Modules\Blog\Services;

use App\Models\User;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Seo\Services\SeoMetaManager;
use App\Services\Audit\AuditLogService;
use App\Services\Content\ContentSanitizerService;
use App\Services\Content\StructuredContentBlockService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;

class BlogPostService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ContentSanitizerService $contentSanitizer,
        private readonly DatabaseManager $database,
        private readonly SeoMetaManager $seoMetaManager,
        private readonly StructuredContentBlockService $structuredContentBlockService,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $actor, array $attributes): BlogPost
    {
        return $this->database->transaction(function () use ($actor, $attributes): BlogPost {
            $post = BlogPost::query()->create($this->payload($attributes, $actor, null));

            $this->syncRelations($post, $attributes);
            $this->seoMetaManager->upsert($post, is_array($attributes['seo'] ?? null) ? $attributes['seo'] : []);

            $this->auditLogService->record(
                eventType: 'blog.post.created',
                action: 'create_blog_post',
                actor: $actor,
                auditable: $post,
                newValues: [
                    'title' => $post->title,
                    'slug' => $post->slug,
                ],
            );

            return $post->fresh(['category', 'tags', 'author', 'featuredImage', 'seoMeta']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(BlogPost $post, User $actor, array $attributes): BlogPost
    {
        return $this->database->transaction(function () use ($actor, $attributes, $post): BlogPost {
            $oldValues = [
                'title' => $post->title,
                'slug' => $post->slug,
                'visibility' => $post->visibility?->value,
            ];

            $post->forceFill($this->payload($attributes, $actor, $post))->save();

            $this->syncRelations($post, $attributes);
            $this->seoMetaManager->upsert($post, is_array($attributes['seo'] ?? null) ? $attributes['seo'] : []);

            $this->auditLogService->record(
                eventType: 'blog.post.updated',
                action: 'update_blog_post',
                actor: $actor,
                auditable: $post,
                oldValues: $oldValues,
                newValues: [
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'visibility' => $post->visibility?->value,
                ],
            );

            return $post->fresh(['category', 'tags', 'author', 'featuredImage', 'seoMeta']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function payload(array $attributes, User $actor, ?BlogPost $post = null): array
    {
        return [
            'blog_category_id' => $attributes['blog_category_id'] ?? null,
            'author_user_id' => $attributes['author_user_id'] ?? $actor->getKey(),
            'title' => trim((string) $attributes['title']),
            'slug' => trim((string) $attributes['slug']),
            'excerpt' => $this->contentSanitizer->sanitizeNullableText($attributes['excerpt'] ?? null),
            'featured_image_media_id' => $attributes['featured_image_media_id'] ?? null,
            'content_blocks_json' => $this->structuredContentBlockService->normalizeForStorage(
                is_array($attributes['blocks'] ?? null) ? $attributes['blocks'] : [],
                $actor,
            ),
            'featured_flag' => (bool) ($attributes['featured_flag'] ?? false),
            'visibility' => $attributes['visibility'],
            'change_notes' => $this->contentSanitizer->sanitizeNullableText($attributes['change_notes'] ?? null),
            'updated_by' => $actor->getKey(),
            'created_by' => $post->created_by ?? $actor->getKey(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function syncRelations(BlogPost $post, array $attributes): void
    {
        $tagIds = collect(Arr::wrap($attributes['tag_ids'] ?? []))
            ->filter(fn (mixed $id): bool => is_scalar($id) && (int) $id > 0)
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $post->tags()->sync($tagIds);

        $relatedIds = collect(Arr::wrap($attributes['related_post_ids'] ?? []))
            ->filter(fn (mixed $id): bool => is_scalar($id) && (int) $id > 0)
            ->map(fn (mixed $id): int => (int) $id)
            ->reject(fn (int $id): bool => $id === $post->getKey())
            ->unique()
            ->values()
            ->all();

        $post->relatedPosts()->sync($relatedIds);
    }
}
