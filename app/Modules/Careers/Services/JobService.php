<?php

namespace App\Modules\Careers\Services;

use App\Models\User;
use App\Modules\Careers\Models\Job;
use App\Modules\Seo\Services\SeoMetaManager;
use App\Services\Audit\AuditLogService;
use App\Services\Content\ContentSanitizerService;
use Illuminate\Database\DatabaseManager;

class JobService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ContentSanitizerService $contentSanitizer,
        private readonly DatabaseManager $database,
        private readonly SeoMetaManager $seoMetaManager,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function store(Job $job, User $actor, array $attributes): Job
    {
        return $this->database->transaction(function () use ($actor, $attributes, $job): Job {
            $oldValues = $job->exists ? [
                'title' => $job->title,
                'slug' => $job->slug,
                'visibility' => $job->visibility?->value,
            ] : [];

            $job->fill([
                'title' => trim((string) $attributes['title']),
                'slug' => trim((string) $attributes['slug']),
                'summary' => $this->contentSanitizer->sanitizeNullableText($attributes['summary'] ?? null),
                'description' => $this->contentSanitizer->sanitizeRichText($attributes['description'] ?? null),
                'location' => $this->contentSanitizer->sanitizeNullableText($attributes['location'] ?? null),
                'employment_type' => $this->contentSanitizer->sanitizeNullableText($attributes['employment_type'] ?? null),
                'department' => $this->contentSanitizer->sanitizeNullableText($attributes['department'] ?? null),
                'level' => $this->contentSanitizer->sanitizeNullableText($attributes['level'] ?? null),
                'deadline' => $attributes['deadline'] ?? null,
                'featured_flag' => (bool) ($attributes['featured_flag'] ?? false),
                'visibility' => $attributes['visibility'],
                'change_notes' => $this->contentSanitizer->sanitizeNullableText($attributes['change_notes'] ?? null),
                'created_by' => $job->created_by ?? $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->seoMetaManager->upsert($job, is_array($attributes['seo'] ?? null) ? $attributes['seo'] : []);

            $this->auditLogService->record(
                eventType: $job->wasRecentlyCreated ? 'careers.job.created' : 'careers.job.updated',
                action: $job->wasRecentlyCreated ? 'create_job' : 'update_job',
                actor: $actor,
                auditable: $job,
                oldValues: $oldValues,
                newValues: [
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'visibility' => $job->visibility?->value,
                ],
            );

            return $job->fresh('seoMeta');
        });
    }
}
