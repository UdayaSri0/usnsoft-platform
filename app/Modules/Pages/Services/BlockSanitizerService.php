<?php

namespace App\Modules\Pages\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BlockSanitizerService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function sanitize(string $blockType, array $payload): array
    {
        $sanitized = $this->sanitizeRecursive($payload);

        if ($blockType === 'rich_text' && isset($sanitized['content_html'])) {
            $sanitized['content_html'] = $this->sanitizeRichText((string) $sanitized['content_html']);
        }

        if ($blockType === 'video_embed') {
            $sanitized = $this->sanitizeVideoEmbed($sanitized);
        }

        $sanitized = $this->sanitizeLinks($sanitized);

        return $sanitized;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sanitizeVideoEmbed(array $payload): array
    {
        $provider = Str::lower((string) ($payload['provider'] ?? ''));
        $providers = config('cms.video.providers', ['youtube', 'vimeo']);

        if (! in_array($provider, $providers, true)) {
            $payload['provider'] = 'youtube';
        }

        $payload['video_url'] = $this->sanitizeVideoUrl((string) ($payload['video_url'] ?? ''));

        return $payload;
    }

    private function sanitizeRichText(string $html): string
    {
        $allowedTags = (string) config('cms.rich_text.allowed_tags', '<p><br><strong><em><ul><ol><li><a><h2><h3><h4><blockquote>');

        $sanitized = strip_tags($html, $allowedTags);

        // Strip event handlers and style attributes.
        $sanitized = (string) preg_replace('/\son[a-z]+\s*=\s*(["\']).*?\1/iu', '', $sanitized);
        $sanitized = (string) preg_replace('/\sstyle\s*=\s*(["\']).*?\1/iu', '', $sanitized);

        // Remove script-like URLs.
        $sanitized = (string) preg_replace('/href\s*=\s*(["\'])\s*javascript:[^\1]*\1/iu', 'href="#"', $sanitized);
        $sanitized = (string) preg_replace('/href\s*=\s*(["\'])\s*data:[^\1]*\1/iu', 'href="#"', $sanitized);

        return trim($sanitized);
    }

    private function sanitizeVideoUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host)) {
            return null;
        }

        $host = Str::lower($host);

        $allowedHosts = [
            'youtube.com',
            'www.youtube.com',
            'youtu.be',
            'vimeo.com',
            'www.vimeo.com',
            'loom.com',
            'www.loom.com',
        ];

        return in_array($host, $allowedHosts, true) ? $url : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sanitizeLinks(array $payload): array
    {
        $allowedSchemes = config('cms.rich_text.allowed_link_schemes', ['http', 'https', 'mailto', 'tel']);

        foreach (Arr::dot($payload) as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            if (! Str::endsWith($key, ['url', 'link', '_url'])) {
                continue;
            }

            $payload = Arr::set($payload, $key, $this->sanitizeUrl($value, $allowedSchemes));
        }

        return $payload;
    }

    /**
     * @param  list<string>  $allowedSchemes
     */
    private function sanitizeUrl(string $url, array $allowedSchemes): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, '/')) {
            return Str::startsWith($url, '//') ? null : $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = Str::lower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme === '' || ! in_array($scheme, $allowedSchemes, true)) {
            return null;
        }

        return $url;
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    private function sanitizeRecursive(mixed $value): mixed
    {
        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $key => $nested) {
                $sanitized[$key] = $this->sanitizeRecursive($nested);
            }

            return $sanitized;
        }

        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }
}
