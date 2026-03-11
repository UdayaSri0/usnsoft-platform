<?php

namespace App\Modules\Products\Enums;

enum ProductDownloadMode: string
{
    case DirectDownload = 'direct_download';
    case ExternalLink = 'external_link';
    case GithubReleaseLink = 'github_release_link';
    case AppStoreLink = 'app_store_link';
    case PlayStoreLink = 'play_store_link';
    case ManualRequest = 'manual_request';
    case ProtectedPrivateDownload = 'protected_private_download';

    public function requiresExternalUrl(): bool
    {
        return in_array($this, [
            self::ExternalLink,
            self::GithubReleaseLink,
            self::AppStoreLink,
            self::PlayStoreLink,
        ], true);
    }

    public function requiresMediaAsset(): bool
    {
        return in_array($this, [
            self::DirectDownload,
            self::ProtectedPrivateDownload,
        ], true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
