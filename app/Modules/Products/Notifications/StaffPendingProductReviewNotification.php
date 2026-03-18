<?php

namespace App\Modules\Products\Notifications;

use App\Modules\Products\Models\ProductReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StaffPendingProductReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ProductReview $review,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'product_review_pending_moderation',
            'review_id' => $this->review->getKey(),
            'product_id' => $this->review->product_id,
            'product_name' => $this->review->product?->name_current,
            'reviewer_name' => $this->review->user?->name,
            'reviewer_email' => $this->review->user?->email,
            'submitted_at' => optional($this->review->submitted_at)->toIso8601String(),
        ];
    }
}
