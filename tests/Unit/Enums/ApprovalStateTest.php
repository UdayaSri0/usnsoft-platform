<?php

namespace Tests\Unit\Enums;

use App\Enums\ApprovalState;
use PHPUnit\Framework\TestCase;

class ApprovalStateTest extends TestCase
{
    public function test_pending_review_state_allows_expected_transitions(): void
    {
        $this->assertTrue(ApprovalState::PendingReview->canTransitionTo(ApprovalState::Approved));
        $this->assertTrue(ApprovalState::PendingReview->canTransitionTo(ApprovalState::Rejected));
        $this->assertTrue(ApprovalState::PendingReview->canTransitionTo(ApprovalState::ChangesRequested));
        $this->assertFalse(ApprovalState::PendingReview->canTransitionTo(ApprovalState::Draft));
    }

    public function test_final_states_do_not_allow_further_transitions(): void
    {
        $this->assertTrue(ApprovalState::Approved->isFinal());
        $this->assertFalse(ApprovalState::Approved->canTransitionTo(ApprovalState::PendingReview));
        $this->assertFalse(ApprovalState::Rejected->canTransitionTo(ApprovalState::PendingReview));
    }
}
