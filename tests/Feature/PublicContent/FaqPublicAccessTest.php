<?php

namespace Tests\Feature\PublicContent;

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\CoreRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PublicContent\Concerns\InteractsWithPublicContent;
use Tests\TestCase;

class FaqPublicAccessTest extends TestCase
{
    use InteractsWithPublicContent;
    use RefreshDatabase;

    public function test_faq_page_filters_by_search_and_hides_unpublished_entries(): void
    {
        $this->seedPublicContentCore();

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $billing = $this->createFaqCategory($superAdmin, ['name' => 'Billing', 'slug' => 'billing']);
        $support = $this->createFaqCategory($superAdmin, ['name' => 'Support', 'slug' => 'support']);

        $this->createFaq($superAdmin, [
            'category' => $billing,
            'question' => 'How is billing approved?',
            'answer' => '<p>Billing follows the approval workflow.</p>',
        ]);

        $this->createFaq($superAdmin, [
            'category' => $support,
            'question' => 'How do internal escalation notes work?',
            'answer' => '<p>These notes stay private.</p>',
            'workflow_state' => ContentWorkflowState::Draft->value,
            'approval_state' => ApprovalState::Draft->value,
            'approved_by' => null,
            'approved_at' => null,
            'published_by' => null,
            'published_at' => null,
        ]);

        $this->get(route('faq.index', ['q' => 'billing', 'category' => 'billing']))
            ->assertOk()
            ->assertSee('How is billing approved?')
            ->assertDontSee('How do internal escalation notes work?');

        $this->get(route('faq.index'))
            ->assertOk()
            ->assertSee('How is billing approved?')
            ->assertDontSee('How do internal escalation notes work?');
    }
}
