<?php

namespace App\Services\Content;

use Illuminate\Support\Str;

class ContentSanitizerService
{
    public function sanitizeNullableText(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    public function sanitizeRichText(mixed $value): ?string
    {
        $html = $this->sanitizeNullableText($value);

        if ($html === null) {
            return null;
        }

        $allowedTags = (string) config('cms.rich_text.allowed_tags', '<p><br><strong><em><ul><ol><li><a><h2><h3><h4><blockquote>');
        $sanitized = strip_tags($html, $allowedTags);
        $sanitized = (string) preg_replace('/\son[a-z]+\s*=\s*(["\']).*?\1/iu', '', $sanitized);
        $sanitized = (string) preg_replace('/\sstyle\s*=\s*(["\']).*?\1/iu', '', $sanitized);
        $sanitized = (string) preg_replace('/href\s*=\s*(["\'])\s*javascript:[^\1]*\1/iu', 'href="#"', $sanitized);
        $sanitized = (string) preg_replace('/href\s*=\s*(["\'])\s*data:[^\1]*\1/iu', 'href="#"', $sanitized);

        return trim($sanitized) !== '' ? trim($sanitized) : null;
    }

    /**
     * @param  list<string>  $allowedSchemes
     */
    public function sanitizeUrl(mixed $value, array $allowedSchemes = ['http', 'https']): ?string
    {
        $url = $this->sanitizeNullableText($value);

        if ($url === null) {
            return null;
        }

        if (Str::startsWith($url, '/')) {
            return Str::startsWith($url, '//') ? null : $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = Str::lower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, $allowedSchemes, true) ? $url : null;
    }

    public function sanitizeVideoUrl(mixed $value): ?string
    {
        $url = $this->sanitizeUrl($value);

        if ($url === null) {
            return null;
        }

        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, [
            'youtube.com',
            'www.youtube.com',
            'youtu.be',
            'vimeo.com',
            'www.vimeo.com',
            'loom.com',
            'www.loom.com',
        ], true) ? $url : null;
    }
}
