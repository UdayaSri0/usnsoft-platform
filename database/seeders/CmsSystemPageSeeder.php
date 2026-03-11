<?php

namespace Database\Seeders;

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Modules\Pages\Enums\PageType;
use App\Modules\Pages\Models\BlockDefinition;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\PageVersion;
use App\Modules\Pages\Services\BlockValidationService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CmsSystemPageSeeder extends Seeder
{
    public function __construct(
        private readonly BlockValidationService $blockValidationService,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            [
                'key' => 'home',
                'title' => 'USNsoft Home',
                'slug' => 'home',
                'path' => '/',
                'is_home' => true,
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    ['type' => 'hero', 'data' => ['title' => 'Build with confidence using USNsoft', 'subtitle' => 'Software, networking, and security delivery in one platform']],
                    ['type' => 'services_block', 'data' => ['title' => 'What we deliver', 'intro' => 'Software engineering, networking architecture, and security operations.']],
                    ['type' => 'cta', 'data' => ['title' => 'Start your project request', 'primary_label' => 'Send Request', 'primary_url' => '/client-requests/new']],
                ],
            ],
            [
                'key' => 'about',
                'title' => 'About USNsoft',
                'slug' => 'about',
                'path' => '/about',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    ['type' => 'rich_text', 'data' => ['content_html' => '<h2>About USNsoft</h2><p>USNsoft designs and delivers practical software and infrastructure solutions for growth-focused organizations.</p>']],
                ],
            ],
            [
                'key' => 'services',
                'title' => 'Services',
                'slug' => 'services',
                'path' => '/services',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    ['type' => 'services_block', 'data' => ['title' => 'Services', 'intro' => 'From architecture to operations, delivered by cross-functional teams.']],
                ],
            ],
            [
                'key' => 'contact',
                'title' => 'Contact',
                'slug' => 'contact',
                'path' => '/contact',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    ['type' => 'contact_section', 'data' => ['title' => 'Contact USNsoft', 'intro' => 'Send an inquiry and our team will respond shortly.', 'show_form' => true]],
                ],
            ],
            [
                'key' => 'faq',
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'path' => '/faq',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    ['type' => 'faq_list', 'data' => ['title' => 'FAQ', 'items' => [['question' => 'How can I request a project?', 'answer' => 'Use the request form and our team will follow up.'], ['question' => 'Do you support enterprise delivery?', 'answer' => 'Yes. We support phased enterprise engagements.']]]],
                ],
            ],
            [
                'key' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'path' => '/privacy-policy',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    ['type' => 'rich_text', 'data' => ['content_html' => '<h2>Privacy Policy</h2><p>This is the initial privacy policy placeholder for editorial refinement.</p>']],
                ],
            ],
            [
                'key' => 'terms',
                'title' => 'Terms and Conditions',
                'slug' => 'terms',
                'path' => '/terms',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    ['type' => 'rich_text', 'data' => ['content_html' => '<h2>Terms and Conditions</h2><p>This is the initial terms placeholder for editorial refinement.</p>']],
                ],
            ],
        ];
    }

    public function run(): void
    {
        $definitions = BlockDefinition::query()->pluck('id', 'key');

        foreach ($this->pages() as $pageData) {
            $page = Page::query()->firstOrCreate(
                ['key' => $pageData['key']],
                [
                    'page_type' => PageType::System,
                    'title_current' => $pageData['title'],
                    'slug_current' => trim($pageData['slug'], '/'),
                    'path_current' => $pageData['path'],
                    'is_home' => (bool) ($pageData['is_home'] ?? false),
                    'is_system_page' => (bool) ($pageData['is_system_page'] ?? true),
                    'is_locked_slug' => (bool) ($pageData['is_locked_slug'] ?? true),
                    'is_active' => true,
                ],
            );

            $version = $page->versions()->firstOrCreate(
                ['version_number' => 1],
                [
                    'title' => $pageData['title'],
                    'slug' => trim($pageData['slug'], '/'),
                    'path' => $pageData['path'],
                    'workflow_state' => ContentWorkflowState::Published,
                    'approval_state' => ApprovalState::Approved,
                    'preview_confirmed_at' => CarbonImmutable::now(),
                    'approved_at' => CarbonImmutable::now(),
                    'published_at' => CarbonImmutable::now(),
                ],
            );

            $version->blocks()->delete();

            foreach (($pageData['blocks'] ?? []) as $index => $block) {
                $definitionId = $definitions[$block['type']] ?? null;

                if (! $definitionId) {
                    continue;
                }

                $payload = $this->blockValidationService->validateAndNormalize($block['type'], Arr::wrap($block['data'] ?? []));

                $version->blocks()->create([
                    'block_definition_id' => $definitionId,
                    'region_key' => 'main',
                    'sort_order' => $index + 1,
                    'internal_name' => ucfirst(str_replace('_', ' ', (string) $block['type'])),
                    'is_enabled' => true,
                    'data_json' => $payload,
                    'layout_json' => config('cms.definitions.'.$block['type'].'.default_layout', []),
                    'visibility_json' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
                ]);
            }

            $page->forceFill([
                'current_draft_version_id' => $version->getKey(),
                'current_published_version_id' => $version->getKey(),
                'title_current' => $version->title,
                'slug_current' => $version->slug,
                'path_current' => $version->path,
                'is_active' => true,
            ])->save();
        }
    }
}
