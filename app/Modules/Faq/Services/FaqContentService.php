<?php

namespace App\Modules\Faq\Services;

use App\Models\User;
use App\Modules\Faq\Models\Faq;
use App\Services\Audit\AuditLogService;
use App\Services\Content\ContentSanitizerService;
use Illuminate\Database\DatabaseManager;

class FaqContentService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly ContentSanitizerService $contentSanitizer,
        private readonly DatabaseManager $database,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function store(Faq $faq, User $actor, array $attributes): Faq
    {
        return $this->database->transaction(function () use ($actor, $attributes, $faq): Faq {
            $oldValues = $faq->exists ? [
                'question' => $faq->question,
                'visibility' => $faq->visibility?->value,
            ] : [];

            $faq->fill([
                'faq_category_id' => $attributes['faq_category_id'] ?? null,
                'linked_product_id' => $attributes['linked_product_id'] ?? null,
                'question' => trim((string) $attributes['question']),
                'answer' => $this->contentSanitizer->sanitizeRichText($attributes['answer'] ?? null),
                'sort_order' => (int) ($attributes['sort_order'] ?? 0),
                'featured_flag' => (bool) ($attributes['featured_flag'] ?? false),
                'visibility' => $attributes['visibility'],
                'change_notes' => $this->contentSanitizer->sanitizeNullableText($attributes['change_notes'] ?? null),
                'created_by' => $faq->created_by ?? $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();

            $this->auditLogService->record(
                eventType: $faq->wasRecentlyCreated ? 'faq.created' : 'faq.updated',
                action: $faq->wasRecentlyCreated ? 'create_faq' : 'update_faq',
                actor: $actor,
                auditable: $faq,
                oldValues: $oldValues,
                newValues: [
                    'question' => $faq->question,
                    'visibility' => $faq->visibility?->value,
                ],
            );

            return $faq->fresh(['category', 'linkedProduct']);
        });
    }
}
