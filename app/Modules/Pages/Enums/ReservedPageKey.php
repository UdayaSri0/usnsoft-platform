<?php

namespace App\Modules\Pages\Enums;

enum ReservedPageKey: string
{
    case Home = 'home';
    case About = 'about';
    case Services = 'services';
    case Contact = 'contact';
    case Faq = 'faq';
    case PrivacyPolicy = 'privacy-policy';
    case Terms = 'terms';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
