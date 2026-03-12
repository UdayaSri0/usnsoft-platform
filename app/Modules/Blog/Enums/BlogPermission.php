<?php

namespace App\Modules\Blog\Enums;

enum BlogPermission: string
{
    case View = 'blog.view';
    case Create = 'blog.create';
    case Update = 'blog.update';
    case Preview = 'blog.preview';
    case SubmitReview = 'blog.submit_review';
    case Approve = 'blog.approve';
    case Reject = 'blog.reject';
    case Publish = 'blog.publish';
    case Schedule = 'blog.schedule';
    case Archive = 'blog.archive';
    case CategoriesManage = 'blog.categories.manage';
    case TagsManage = 'blog.tags.manage';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
