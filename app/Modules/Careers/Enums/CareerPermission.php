<?php

namespace App\Modules\Careers\Enums;

enum CareerPermission: string
{
    case View = 'careers.view';
    case Create = 'careers.create';
    case Update = 'careers.update';
    case SubmitReview = 'careers.submit_review';
    case Approve = 'careers.approve';
    case Reject = 'careers.reject';
    case Publish = 'careers.publish';
    case Schedule = 'careers.schedule';
    case Archive = 'careers.archive';
    case ApplicationsView = 'careers.applications.view';
    case ApplicationsUpdate = 'careers.applications.update';
    case ApplicationsNotesManage = 'careers.applications.notes.manage';
    case ApplicationsFilesView = 'careers.applications.files.view';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
