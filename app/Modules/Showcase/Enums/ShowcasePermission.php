<?php

namespace App\Modules\Showcase\Enums;

enum ShowcasePermission: string
{
    case TestimonialsManage = 'showcase.testimonials.manage';
    case PartnersManage = 'showcase.partners.manage';
    case TeamManage = 'showcase.team.manage';
    case TimelineManage = 'showcase.timeline.manage';
    case AchievementsManage = 'showcase.achievements.manage';
    case SubmitReview = 'showcase.submit_review';
    case Approve = 'showcase.approve';
    case Reject = 'showcase.reject';
    case Publish = 'showcase.publish';
    case Schedule = 'showcase.schedule';
    case Archive = 'showcase.archive';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
