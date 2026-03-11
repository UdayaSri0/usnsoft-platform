<?php

namespace App\Modules\Products\Enums;

enum ProductKind: string
{
    case DesktopApp = 'desktop_app';
    case MobileApp = 'mobile_app';
    case WebApp = 'web_app';
    case Plugin = 'plugin';
    case InternalTool = 'internal_tool';
    case OpenSourceProject = 'open_source_project';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
