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
use Illuminate\Support\Str;

class CmsSystemPageSeeder extends Seeder
{
    public function __construct(
        private readonly BlockValidationService $blockValidationService,
    ) {}

    private function mediaId(string $seed): string
    {
        $normalized = strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $seed));

        return str_pad(substr($normalized, 0, 26), 26, '0');
    }

    /**
     * @param  list<string>  $paragraphs
     * @param  list<string>  $bullets
     */
    private function richText(string $heading, array $paragraphs, array $bullets = []): string
    {
        $html = '<h2>'.$heading.'</h2>';

        foreach ($paragraphs as $paragraph) {
            $html .= '<p>'.$paragraph.'</p>';
        }

        if ($bullets !== []) {
            $html .= '<ul>';

            foreach ($bullets as $bullet) {
                $html .= '<li>'.$bullet.'</li>';
            }

            $html .= '</ul>';
        }

        return $html;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            [
                'key' => 'home',
                'title' => 'USNsoft Home',
                'summary' => 'Modern software, networking, and security delivery in one platform.',
                'slug' => 'home',
                'path' => '/',
                'is_home' => true,
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Enterprise platform delivery',
                            'badge' => 'Single codebase, security-first',
                            'title' => 'Corporate websites, customer access, and internal workflows with one secure platform foundation.',
                            'subtitle' => 'USNsoft combines software delivery, network thinking, and operational security into a coherent business platform.',
                            'body' => 'Deliver fast public experiences and safe internal operations without splitting responsibilities across disconnected systems.',
                            'primary_cta_label' => 'Start client request',
                            'primary_cta_url' => '/client-request',
                            'secondary_cta_label' => 'Explore products',
                            'secondary_cta_url' => '/products',
                            'trust_items' => [
                                'Approval-aware publishing',
                                'Protected customer access',
                                'Operational runbooks built in',
                            ],
                        ],
                    ],
                    [
                        'type' => 'partner_logos',
                        'data' => [
                            'title' => 'Built for teams that need dependable delivery',
                            'items' => [
                                ['media_id' => $this->mediaId('partner-alpha'), 'name' => 'Alpha Finance'],
                                ['media_id' => $this->mediaId('partner-bravo'), 'name' => 'Northline Ops'],
                                ['media_id' => $this->mediaId('partner-charlie'), 'name' => 'Vector Health'],
                                ['media_id' => $this->mediaId('partner-delta'), 'name' => 'Atlas Retail'],
                                ['media_id' => $this->mediaId('partner-echo'), 'name' => 'Bridge Logistics'],
                                ['media_id' => $this->mediaId('partner-foxtrot'), 'name' => 'Crest Utilities'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'services_block',
                        'data' => [
                            'title' => 'What USNsoft delivers',
                            'intro' => 'Practical execution across software engineering, infrastructure operations, and business-safe security controls.',
                            'cta_label' => 'View services',
                            'cta_url' => '/services',
                        ],
                    ],
                    [
                        'type' => 'product_grid',
                        'data' => [
                            'title' => 'Core platform products',
                            'intro' => 'Delivery layers and reusable platform capabilities for growth-focused organizations.',
                            'item_limit' => 3,
                            'show_cta' => true,
                        ],
                    ],
                    [
                        'type' => 'stat_counters',
                        'data' => [
                            'title' => 'Operational confidence by default',
                            'stats' => [
                                ['number' => 1, 'suffix' => ' codebase', 'label' => 'Unified platform', 'description' => 'Public site and admin workflows stay aligned.'],
                                ['number' => 8, 'suffix' => '+', 'label' => 'Core roles', 'description' => 'Privilege boundaries stay explicit and auditable.'],
                                ['number' => 24, 'suffix' => '/7', 'label' => 'Process visibility', 'description' => 'Queues, logs, and scheduled tasks remain observable.'],
                                ['number' => 100, 'suffix' => '%', 'label' => 'Safe blocks', 'description' => 'Structured CMS blocks prevent unsafe runtime content.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'testimonial_list',
                        'data' => [
                            'title' => 'Why teams choose USNsoft',
                            'items' => [
                                ['author' => 'Operations Director', 'role' => 'Regional Services Firm', 'quote' => 'USNsoft gave us a sharper public site and a safer internal workflow model at the same time.'],
                                ['author' => 'IT Manager', 'role' => 'Infrastructure Provider', 'quote' => 'The platform feels deliberate. Permissions, approvals, and content operations finally behave like one system.'],
                                ['author' => 'Commercial Lead', 'role' => 'B2B Technology Team', 'quote' => 'We needed customer polish without sacrificing internal governance, and that balance is exactly what landed well.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'Start with a clear delivery conversation',
                            'body' => 'Share your requirements and get a structured response covering scope, priorities, and next steps.',
                            'primary_label' => 'Open client request',
                            'primary_url' => '/client-request',
                            'secondary_label' => 'Contact USNsoft',
                            'secondary_url' => '/contact',
                            'supporting_note' => 'Security-first, enterprise-ready, and built for long-term maintenance.',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'about',
                'title' => 'About USNsoft',
                'summary' => 'Company background, delivery philosophy, and leadership focus.',
                'slug' => 'about',
                'path' => '/about',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'About USNsoft',
                            'title' => 'A delivery partner focused on durable software, clear operations, and controlled growth.',
                            'subtitle' => 'We build business systems that are practical for staff, credible for customers, and maintainable for engineering teams.',
                            'primary_cta_label' => 'Talk to us',
                            'primary_cta_url' => '/contact',
                            'secondary_cta_label' => 'See services',
                            'secondary_cta_url' => '/services',
                            'trust_items' => ['Platform discipline', 'Operational clarity', 'Security awareness'],
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'content_html' => $this->richText(
                                'What guides the platform',
                                [
                                    'USNsoft designs and delivers practical software and infrastructure solutions for growth-focused organizations.',
                                    'The platform approach is deliberate: public website presentation, internal operations, publishing governance, and customer account flows stay within one maintainable Laravel codebase.',
                                ],
                                [
                                    'Keep privileged actions explicit and reviewable.',
                                    'Design for maintainability from the first release.',
                                    'Make the interface feel polished without turning it into a fragile demo.',
                                ],
                            ),
                        ],
                    ],
                    [
                        'type' => 'timeline',
                        'data' => [
                            'title' => 'How engagements mature',
                            'items' => [
                                ['date' => 'Phase 01', 'title' => 'Discovery and architecture', 'body' => 'Map business goals, risk boundaries, and the operational shape of the system.'],
                                ['date' => 'Phase 02', 'title' => 'Build and integrate', 'body' => 'Deliver public and internal features without fragmenting the codebase.'],
                                ['date' => 'Phase 03', 'title' => 'Govern and refine', 'body' => 'Operationalize approvals, audits, queue processing, and maintenance routines.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'team_cards',
                        'data' => [
                            'title' => 'Leadership emphasis',
                            'items' => [
                                ['name' => 'Platform Architecture', 'role' => 'System direction', 'bio' => 'Keeps modular boundaries clear while maintaining one coherent product surface.'],
                                ['name' => 'Delivery Operations', 'role' => 'Execution quality', 'bio' => 'Aligns engineering throughput with real operational needs and recovery paths.'],
                                ['name' => 'Security Governance', 'role' => 'Risk and controls', 'bio' => 'Ensures privileges, approvals, and access flows remain safe for staff and customers.'],
                                ['name' => 'Client Enablement', 'role' => 'Commercial clarity', 'bio' => 'Translates delivery capability into clear, usable service and request experiences.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'See how the platform applies to your organization',
                            'body' => 'Discuss scope, internal workflows, and customer-facing experience with the USNsoft team.',
                            'primary_label' => 'Book a discussion',
                            'primary_url' => '/contact',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'services',
                'title' => 'Services',
                'summary' => 'Software, infrastructure, and security services delivered through one platform approach.',
                'slug' => 'services',
                'path' => '/services',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Services',
                            'title' => 'Cross-functional delivery for teams that need more than a brochure website.',
                            'subtitle' => 'USNsoft bridges public presentation, internal workflows, and operational safeguards in one platform program.',
                            'primary_cta_label' => 'Request consultation',
                            'primary_cta_url' => '/client-request',
                            'secondary_cta_label' => 'Contact team',
                            'secondary_cta_url' => '/contact',
                            'trust_items' => ['Software delivery', 'Infrastructure support', 'Security operations'],
                        ],
                    ],
                    [
                        'type' => 'feature_grid',
                        'data' => [
                            'title' => 'Service lanes',
                            'intro' => 'The work stays structured so stakeholders, operators, and developers are all working with the same system context.',
                            'items' => [
                                ['title' => 'Platform strategy', 'body' => 'Plan public site, account flows, and admin operations as one product.' ],
                                ['title' => 'Web engineering', 'body' => 'Deliver modern Laravel interfaces with reusable components and maintainable patterns.' ],
                                ['title' => 'Operational hardening', 'body' => 'Improve queue, scheduler, logging, and recovery readiness.' ],
                                ['title' => 'Content governance', 'body' => 'Apply approval-driven publishing and safe block-based composition.' ],
                                ['title' => 'Access control', 'body' => 'Preserve privilege boundaries with explicit roles, permissions, and policy checks.' ],
                                ['title' => 'Maintenance support', 'body' => 'Document local development, debugging, and runbook practices for long-term ownership.' ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'services_block',
                        'data' => [
                            'title' => 'Delivery services',
                            'intro' => 'From architecture to operations, delivered by cross-functional teams.',
                            'cta_label' => 'Discuss scope',
                            'cta_url' => '/client-request',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'Need a more controlled delivery model?',
                            'body' => 'Bring your public site, internal operations, and customer access requirements into one engagement.',
                            'primary_label' => 'Start a scoped request',
                            'primary_url' => '/client-request',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'products',
                'title' => 'Products',
                'summary' => 'Platform product offerings and delivery layers.',
                'slug' => 'products',
                'path' => '/products',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Products',
                            'title' => 'Platform capabilities that keep public and internal experiences working as one system.',
                            'subtitle' => 'USNsoft product layers support secure websites, controlled publishing, customer access, and operational maintenance.',
                            'primary_cta_label' => 'See product detail',
                            'primary_cta_url' => '/products/platform-security-suite',
                            'secondary_cta_label' => 'Request product consultation',
                            'secondary_cta_url' => '/client-request',
                            'trust_items' => ['Unified architecture', 'Reusable blocks', 'Protected workflows'],
                        ],
                    ],
                    [
                        'type' => 'product_grid',
                        'data' => [
                            'title' => 'Platform products',
                            'intro' => 'Core capabilities for modern corporate delivery without repo sprawl or unsafe content tooling.',
                            'item_limit' => 4,
                        ],
                    ],
                    [
                        'type' => 'feature_grid',
                        'data' => [
                            'title' => 'What these products emphasize',
                            'items' => [
                                ['title' => 'Single codebase discipline', 'body' => 'Keep public, authenticated, and administrative flows aligned through one maintainable stack.'],
                                ['title' => 'Protected customer access', 'body' => 'Downloads and request flows stay authorization-aware and verification-ready.'],
                                ['title' => 'Publishing governance', 'body' => 'Use preview, approval, schedule, and publish states instead of raw runtime editing.'],
                                ['title' => 'Operational clarity', 'body' => 'Queues, scheduler, docs, and recovery commands are part of the product, not an afterthought.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'Evaluate the product fit for your environment',
                            'body' => 'We can map product capabilities to your operational and governance requirements.',
                            'primary_label' => 'Talk to product team',
                            'primary_url' => '/client-request',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'product-platform-security-suite',
                'title' => 'Operational Security Suite',
                'summary' => 'A product detail page for platform security and access governance capabilities.',
                'slug' => 'platform-security-suite',
                'path' => '/products/platform-security-suite',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Product detail',
                            'title' => 'Operational Security Suite',
                            'subtitle' => 'Visibility, authorization, and safe operational controls for public and internal platform workflows.',
                            'body' => 'Built for organizations that need customer-facing polish without sacrificing role boundaries, approval discipline, or long-term recoverability.',
                            'primary_cta_label' => 'Request implementation discussion',
                            'primary_cta_url' => '/client-request',
                            'secondary_cta_label' => 'Contact USNsoft',
                            'secondary_cta_url' => '/contact',
                            'trust_items' => ['Session history', 'Approval checks', 'Protected downloads'],
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'content_html' => $this->richText(
                                'Built for operational confidence',
                                [
                                    'The Operational Security Suite focuses on the layers teams usually bolt on too late: privilege boundaries, account state handling, auditability, queue-backed notification paths, and predictable recovery routines.',
                                    'It is designed to fit naturally inside one Laravel platform rather than forcing a second admin frontend or unsafe runtime content behavior.',
                                ],
                            ),
                        ],
                    ],
                    [
                        'type' => 'feature_grid',
                        'data' => [
                            'title' => 'Suite capabilities',
                            'items' => [
                                ['title' => 'Policy-first authorization', 'body' => 'Gate, policy, and middleware boundaries stay explicit for privileged actions.'],
                                ['title' => 'Session and device visibility', 'body' => 'Users and staff can inspect session history and recognized device state.'],
                                ['title' => 'Protected asset access', 'body' => 'Downloads can remain behind verification and permission-aware guards.'],
                                ['title' => 'Audit-ready publishing', 'body' => 'Content approval and publish actions stay reviewable and role-restricted.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'stat_counters',
                        'data' => [
                            'title' => 'Security posture summary',
                            'stats' => [
                                ['number' => 4, 'suffix' => ' layers', 'label' => 'Access visibility', 'description' => 'Sessions, devices, permissions, and account status.'],
                                ['number' => 1, 'suffix' => ' flow', 'label' => 'Approval chain', 'description' => 'Preview to approval to publish stays structured.'],
                                ['number' => 0, 'label' => 'Unsafe runtime blocks', 'description' => 'Structured CMS rendering avoids executable admin content.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'file_download_block',
                        'data' => [
                            'title' => 'Sample documentation pack',
                            'body' => 'Protected and public document handling can be modeled without weakening authorization awareness.',
                            'files' => [
                                ['media_id' => $this->mediaId('security-overview-pack'), 'label' => 'Security overview pack', 'access_mode' => 'protected', 'require_login' => true],
                                ['media_id' => $this->mediaId('implementation-checklist'), 'label' => 'Implementation checklist', 'access_mode' => 'public', 'require_login' => false],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'Discuss product rollout and governance needs',
                            'body' => 'We can tailor the implementation to your staff roles, approval model, and protected asset requirements.',
                            'primary_label' => 'Start a request',
                            'primary_url' => '/client-request',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'contact',
                'title' => 'Contact',
                'summary' => 'How to reach USNsoft and start a structured enquiry.',
                'slug' => 'contact',
                'path' => '/contact',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Contact',
                            'title' => 'Reach the team with clear context and get a structured response.',
                            'subtitle' => 'Use the intake paths that match your request type so delivery, operations, and security discussions start cleanly.',
                            'primary_cta_label' => 'Client request page',
                            'primary_cta_url' => '/client-request',
                            'secondary_cta_label' => 'Read FAQ',
                            'secondary_cta_url' => '/faq',
                            'trust_items' => ['Clear triage', 'Secure handling', 'Practical next steps'],
                        ],
                    ],
                    [
                        'type' => 'contact_section',
                        'data' => [
                            'title' => 'Contact USNsoft',
                            'intro' => 'Send an inquiry and our team will respond shortly.',
                            'email' => 'hello@usnsoft.test',
                            'phone' => '+1 (555) 010-2026',
                            'hours' => 'Monday to Friday, 09:00 to 18:00',
                            'address' => 'Remote-first delivery with Colombo-based coordination',
                            'show_form' => true,
                            'form_type' => 'contact',
                        ],
                    ],
                    [
                        'type' => 'faq_list',
                        'data' => [
                            'title' => 'Before you reach out',
                            'items' => [
                                ['question' => 'Can we start with a scoped discovery discussion?', 'answer' => 'Yes. We recommend starting with the current business goals, constraints, and the expected ownership model.'],
                                ['question' => 'Do you handle both public UI and internal workflows?', 'answer' => 'Yes. The platform is designed to keep those experiences aligned in one system.'],
                                ['question' => 'Can security and approval requirements be part of the first phase?', 'answer' => 'They should be. Governance works best when it is included in the initial design.'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'careers',
                'title' => 'Careers',
                'summary' => 'Career page for future openings and team culture positioning.',
                'slug' => 'careers',
                'path' => '/careers',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Careers',
                            'title' => 'Join a team building serious platform systems with clear operational intent.',
                            'subtitle' => 'We value practical engineering, thoughtful design, and responsibility for how software behaves after launch.',
                            'primary_cta_label' => 'Contact about careers',
                            'primary_cta_url' => '/contact',
                            'secondary_cta_label' => 'Learn about USNsoft',
                            'secondary_cta_url' => '/about',
                            'trust_items' => ['Engineering ownership', 'Operational thinking', 'Long-term product care'],
                        ],
                    ],
                    [
                        'type' => 'feature_grid',
                        'data' => [
                            'title' => 'What the work emphasizes',
                            'items' => [
                                ['title' => 'Systems thinking', 'body' => 'Work across public UI, internal operations, and security-aware architecture.'],
                                ['title' => 'Maintainability', 'body' => 'Design for a platform that other developers and staff can operate confidently.'],
                                ['title' => 'Business realism', 'body' => 'Keep the product useful for non-technical teams without diluting engineering standards.'],
                                ['title' => 'Operational rigor', 'body' => 'Treat documentation, debugging, and recovery capability as first-class work.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'team_cards',
                        'data' => [
                            'title' => 'Ways to contribute',
                            'items' => [
                                ['name' => 'Platform engineer', 'role' => 'Architecture and delivery', 'bio' => 'Build reusable, secure application foundations with strong operational defaults.'],
                                ['name' => 'UI engineer', 'role' => 'Public and internal interfaces', 'bio' => 'Create polished, responsive, and maintainable Blade/Tailwind experiences.'],
                                ['name' => 'Operations specialist', 'role' => 'Runtime and support', 'bio' => 'Improve deployment, scheduling, queue health, and recovery discipline.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'No role posted that fits?',
                            'body' => 'Reach out with your background and the kind of problems you like to solve.',
                            'primary_label' => 'Contact careers team',
                            'primary_url' => '/contact',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'blog',
                'title' => 'Blog & News',
                'summary' => 'Insights and updates from the platform and delivery practice.',
                'slug' => 'blog',
                'path' => '/blog',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Blog & News',
                            'title' => 'Insights on platform delivery, governance, and operational clarity.',
                            'subtitle' => 'A place to capture the thinking behind design decisions, architecture tradeoffs, and delivery discipline.',
                            'primary_cta_label' => 'Contact editorial team',
                            'primary_cta_url' => '/contact',
                            'secondary_cta_label' => 'Explore products',
                            'secondary_cta_url' => '/products',
                            'trust_items' => ['Security', 'Architecture', 'Operations'],
                        ],
                    ],
                    [
                        'type' => 'blog_teaser',
                        'data' => [
                            'title' => 'Latest updates',
                            'intro' => 'Current platform and delivery notes from the USNsoft team.',
                            'item_limit' => 3,
                            'show_excerpt' => true,
                            'show_date' => true,
                            'show_author' => true,
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'Want a deeper technical conversation?',
                            'body' => 'Use the contact flow to discuss architecture, operations, or product fit.',
                            'primary_label' => 'Contact USNsoft',
                            'primary_url' => '/contact',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'faq',
                'title' => 'Frequently Asked Questions',
                'summary' => 'Common questions about delivery, scope, and platform operations.',
                'slug' => 'faq',
                'path' => '/faq',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'FAQ',
                            'title' => 'Straight answers about requests, delivery, and platform governance.',
                            'subtitle' => 'Use this section to understand how USNsoft approaches scope, approvals, and long-term maintainability.',
                            'primary_cta_label' => 'Open client request',
                            'primary_cta_url' => '/client-request',
                            'secondary_cta_label' => 'Contact team',
                            'secondary_cta_url' => '/contact',
                            'trust_items' => ['Clear boundaries', 'Practical delivery', 'Operational readiness'],
                        ],
                    ],
                    [
                        'type' => 'faq_list',
                        'data' => [
                            'title' => 'Frequently asked questions',
                            'items' => [
                                ['question' => 'How can I request a project?', 'answer' => 'Use the structured client request page so our team gets the right context from the start.'],
                                ['question' => 'Do you support enterprise delivery expectations?', 'answer' => 'Yes. The platform direction is intentionally enterprise-ready, security-aware, and designed for long-term maintenance.'],
                                ['question' => 'Can the public site and admin remain in one codebase?', 'answer' => 'Yes. That is part of the architecture discipline and avoids unnecessary fragmentation.'],
                                ['question' => 'Do you allow arbitrary executable admin content?', 'answer' => 'No. The CMS uses safe, structured blocks rather than raw executable runtime content.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'Still need clarification?',
                            'body' => 'Bring your questions into a direct discussion with the USNsoft team.',
                            'primary_label' => 'Contact team',
                            'primary_url' => '/contact',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'client-request',
                'title' => 'Client Request',
                'summary' => 'Public guidance for initiating structured client requests.',
                'slug' => 'client-request',
                'path' => '/client-request',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Client request',
                            'title' => 'Start the conversation with business context, technical constraints, and expected outcomes.',
                            'subtitle' => 'Structured request intake helps both sides move faster and keeps the eventual delivery path more predictable.',
                            'primary_cta_label' => 'Create account',
                            'primary_cta_url' => '/register',
                            'secondary_cta_label' => 'Contact team',
                            'secondary_cta_url' => '/contact',
                            'trust_items' => ['Business goals', 'Security constraints', 'Delivery timing'],
                        ],
                    ],
                    [
                        'type' => 'form_block',
                        'data' => [
                            'form_type' => 'project_inquiry',
                            'title' => 'Prepare a project inquiry',
                            'intro' => 'The platform uses safe internal handlers for public form submission and follow-up routing.',
                            'anti_spam_enabled' => true,
                        ],
                    ],
                    [
                        'type' => 'feature_grid',
                        'data' => [
                            'title' => 'Helpful request details',
                            'items' => [
                                ['title' => 'Business objectives', 'body' => 'What result matters most and why now?'],
                                ['title' => 'Current platform constraints', 'body' => 'Describe the systems, approvals, or access issues already in play.'],
                                ['title' => 'Users and roles', 'body' => 'Clarify public users, staff groups, and privileged boundaries.'],
                                ['title' => 'Timeline and dependencies', 'body' => 'Share any deadlines, integrations, or operating assumptions up front.'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => 'Ready to move into a scoped request?',
                            'body' => 'Account creation unlocks the authenticated request intake flow.',
                            'primary_label' => 'Register',
                            'primary_url' => '/register',
                            'secondary_label' => 'Log in',
                            'secondary_url' => '/login',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'summary' => 'High-level privacy handling notes for the platform.',
                'slug' => 'privacy-policy',
                'path' => '/privacy-policy',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'content_html' => $this->richText(
                                'Privacy Policy',
                                [
                                    'This platform is designed for secure business operations and customer interactions. Privacy handling should remain proportionate, explicit, and operationally reviewable.',
                                    'Environment-specific configuration, mail delivery, and access control settings must not expose real production secrets in source code or local documentation.',
                                ],
                                [
                                    'Collect only the information required to operate the service and support legitimate workflows.',
                                    'Restrict sensitive access through role and permission boundaries.',
                                    'Treat logs, queues, and uploaded assets with the same operational discipline as application data.',
                                ],
                            ),
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'content_html' => $this->richText(
                                'Operational handling',
                                [
                                    'Local development and demo environments use placeholder credentials and development-only accounts. These are not valid for production use.',
                                    'If you are adapting this platform for real use, legal review and environment-specific privacy controls should be completed before go-live.',
                                ],
                            ),
                        ],
                    ],
                ],
            ],
            [
                'key' => 'terms',
                'title' => 'Terms and Conditions',
                'summary' => 'High-level terms placeholder for platform use and delivery engagement.',
                'slug' => 'terms',
                'path' => '/terms',
                'is_system_page' => true,
                'is_locked_slug' => true,
                'blocks' => [
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'content_html' => $this->richText(
                                'Terms and Conditions',
                                [
                                    'This platform foundation is intended for professional business use. Public, authenticated, and internal workflows are subject to role boundaries and approval constraints where configured.',
                                    'Development-only content, seeded users, and local environment credentials are for non-production use and testing purposes only.',
                                ],
                                [
                                    'Do not rely on local development accounts or placeholder credentials outside non-production environments.',
                                    'Respect privileged action boundaries and approval requirements when operating internal workflows.',
                                    'Review and adapt legal language before production deployment.',
                                ],
                            ),
                        ],
                    ],
                    [
                        'type' => 'rich_text',
                        'data' => [
                            'content_html' => $this->richText(
                                'Operational notes',
                                [
                                    'The platform intentionally avoids unsafe runtime page-builder behavior and executable admin content. Structured blocks, validations, and policy checks are part of the baseline operating model.',
                                ],
                            ),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function run(): void
    {
        $definitions = BlockDefinition::query()->pluck('id', 'key');

        foreach ($this->pages() as $pageData) {
            $page = Page::query()->firstOrNew([
                'key' => $pageData['key'],
            ]);

            if (! $page->exists && ! $page->uuid) {
                $page->uuid = (string) Str::uuid();
            }

            $page->fill([
                'page_type' => PageType::System,
                'title_current' => $pageData['title'],
                'slug_current' => trim($pageData['slug'], '/'),
                'path_current' => $pageData['path'],
                'is_home' => (bool) ($pageData['is_home'] ?? false),
                'is_system_page' => (bool) ($pageData['is_system_page'] ?? true),
                'is_locked_slug' => (bool) ($pageData['is_locked_slug'] ?? true),
                'is_active' => true,
            ])->save();

            $version = $page->versions()->updateOrCreate(
                ['version_number' => 1],
                [
                    'title' => $pageData['title'],
                    'slug' => trim($pageData['slug'], '/'),
                    'path' => $pageData['path'],
                    'summary' => $pageData['summary'] ?? null,
                    'seo_snapshot_json' => $pageData['seo'] ?? [
                        'meta_title' => $pageData['title'].' | USNsoft',
                        'meta_description' => $pageData['summary'] ?? 'USNsoft secure platform delivery.',
                        'canonical_url' => url($pageData['path'] === '/' ? '/' : ltrim($pageData['path'], '/')),
                        'robots_index' => true,
                        'robots_follow' => true,
                    ],
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
