<?php

namespace App\Modules\Products\Enums;

enum ProductPermission: string
{
    case View = 'products.view';
    case Create = 'products.create';
    case Update = 'products.update';
    case Preview = 'products.preview';
    case SubmitReview = 'products.submit_review';
    case Approve = 'products.approve';
    case Reject = 'products.reject';
    case Publish = 'products.publish';
    case Schedule = 'products.schedule';
    case Archive = 'products.archive';
    case CategoriesManage = 'products.categories.manage';
    case TagsManage = 'products.tags.manage';
    case ReviewsModerate = 'products.reviews.moderate';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
