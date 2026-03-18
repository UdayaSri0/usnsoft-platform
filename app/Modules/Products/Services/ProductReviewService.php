<?php

namespace App\Modules\Products\Services;

use App\Models\User;
use App\Modules\Products\Enums\ProductPermission;
use App\Modules\Products\Enums\ProductReviewState;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductReview;
use App\Modules\Products\Notifications\StaffPendingProductReviewNotification;
use App\Services\Audit\AuditLogService;
use App\Services\Notifications\OperationalNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class ProductReviewService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly DatabaseManager $database,
        private readonly ProductReviewEligibilityService $eligibilityService,
        private readonly OperationalNotificationService $notificationService,
    ) {}

    /**
     * @param  array{rating: int, title?: string|null, body: string}  $payload
     */
    public function submit(Product $product, User $user, array $payload): ProductReview
    {
        $eligibility = $this->eligibilityService->evaluate($user, $product);

        if (! $eligibility['allowed']) {
            throw ValidationException::withMessages([
                'review' => $this->eligibilityService->reasonMessage($eligibility['reason']),
            ]);
        }

        $review = ProductReview::query()->create([
            'product_id' => $product->getKey(),
            'user_id' => $user->getKey(),
            'product_user_verification_id' => $eligibility['verification']?->getKey(),
            'rating' => (int) $payload['rating'],
            'title' => $this->sanitizeTitle($payload['title'] ?? null),
            'body' => $this->sanitizeBody((string) $payload['body']),
            'moderation_state' => ProductReviewState::Pending,
            'verification_source' => $eligibility['verification']?->source,
            'submitted_at' => CarbonImmutable::now(),
        ]);

        $this->auditLogService->record(
            eventType: 'products.review.submitted',
            action: 'submit_product_review',
            actor: $user,
            auditable: $review,
            metadata: [
                'product_id' => $product->getKey(),
                'verification_source' => $eligibility['verification']?->source?->value,
            ],
        );

        $review->loadMissing(['product', 'user']);

        $this->notificationService->notifyUsersWithPermission(
            ProductPermission::ReviewsModerate->value,
            new StaffPendingProductReviewNotification($review),
        );

        $this->notificationService->dispatchBusinessEvent('products.review.submitted', [
            'review_id' => $review->getKey(),
            'product_id' => $product->getKey(),
        ]);

        return $review;
    }

    public function moderate(ProductReview $review, ProductReviewState $state, User $actor, ?string $notes = null): ProductReview
    {
        return $this->database->transaction(function () use ($actor, $notes, $review, $state): ProductReview {
            $oldValues = [
                'moderation_state' => $review->moderation_state?->value,
                'moderation_notes' => $review->moderation_notes,
            ];

            $review->forceFill([
                'moderation_state' => $state,
                'moderated_at' => CarbonImmutable::now(),
                'moderated_by' => $actor->getKey(),
                'moderation_notes' => $notes,
                'published_at' => $state === ProductReviewState::Approved ? CarbonImmutable::now() : null,
            ])->save();

            $this->syncAggregates($review->product);

            $this->auditLogService->record(
                eventType: 'products.review.moderated',
                action: 'moderate_product_review',
                actor: $actor,
                auditable: $review,
                oldValues: $oldValues,
                newValues: [
                    'moderation_state' => $state->value,
                    'moderation_notes' => $notes,
                ],
                metadata: [
                    'product_id' => $review->product_id,
                    'notes' => $notes,
                ],
            );

            $this->notificationService->dispatchBusinessEvent('products.review.moderated', [
                'review_id' => $review->getKey(),
                'product_id' => $review->product_id,
                'moderation_state' => $state->value,
            ]);

            return $review->refresh();
        });
    }

    public function syncAggregates(Product $product): void
    {
        $approved = ProductReview::query()
            ->where('product_id', $product->getKey())
            ->where('moderation_state', ProductReviewState::Approved->value);

        $product->forceFill([
            'approved_review_count' => $approved->count(),
            'average_rating' => $approved->avg('rating'),
        ])->save();
    }

    private function sanitizeTitle(?string $title): ?string
    {
        if ($title === null) {
            return null;
        }

        $sanitized = trim(strip_tags($title));

        return $sanitized !== '' ? $sanitized : null;
    }

    private function sanitizeBody(string $body): string
    {
        return trim(strip_tags($body));
    }
}
