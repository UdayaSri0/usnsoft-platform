<?php

namespace App\Modules\Faq\Enums;

enum FaqPermission: string
{
    case View = 'faq.view';
    case Create = 'faq.create';
    case Update = 'faq.update';
    case SubmitReview = 'faq.submit_review';
    case Approve = 'faq.approve';
    case Reject = 'faq.reject';
    case Publish = 'faq.publish';
    case Schedule = 'faq.schedule';
    case Archive = 'faq.archive';
    case CategoriesManage = 'faq.categories.manage';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
