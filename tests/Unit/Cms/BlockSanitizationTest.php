<?php

namespace Tests\Unit\Cms;

use App\Modules\Pages\Services\BlockValidationService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BlockSanitizationTest extends TestCase
{
    public function test_rich_text_payload_is_sanitized_server_side(): void
    {
        $service = app(BlockValidationService::class);

        $sanitized = $service->validateAndNormalize('rich_text', [
            'content_html' => '<p onclick="evil()">Safe</p><script>alert(1)</script><a href="javascript:alert(1)">Click</a>',
        ]);

        $this->assertArrayHasKey('content_html', $sanitized);
        $this->assertStringContainsString('<p>Safe</p>', $sanitized['content_html']);
        $this->assertStringNotContainsString('<script>', $sanitized['content_html']);
        $this->assertStringNotContainsString('onclick', $sanitized['content_html']);
        $this->assertStringNotContainsString('javascript:', $sanitized['content_html']);
        $this->assertStringContainsString('href="#"', $sanitized['content_html']);
    }

    public function test_video_embed_with_disallowed_url_is_rejected(): void
    {
        $service = app(BlockValidationService::class);

        $this->expectException(ValidationException::class);

        $service->validateAndNormalize('video_embed', [
            'provider' => 'unknown-provider',
            'video_url' => 'https://example.com/bad-video',
            'title' => 'Demo',
        ]);
    }

    public function test_unsupported_block_type_is_rejected(): void
    {
        $service = app(BlockValidationService::class);

        $this->expectException(ValidationException::class);

        $service->validateAndNormalize('custom_code', []);
    }
}
