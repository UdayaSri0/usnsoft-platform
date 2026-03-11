<?php

namespace App\Modules\Pages\Enums;

enum CmsPermission: string
{
    case PagesView = 'cms.pages.view';
    case PagesCreate = 'cms.pages.create';
    case PagesUpdate = 'cms.pages.update';
    case PagesDelete = 'cms.pages.delete';
    case PagesCompose = 'cms.pages.compose';
    case PagesReorderBlocks = 'cms.pages.reorder_blocks';
    case PagesUseReusableBlocks = 'cms.pages.use_reusable_blocks';
    case PagesUseAdvancedBlocks = 'cms.pages.use_advanced_blocks';
    case PagesManageHomepage = 'cms.pages.manage_homepage';
    case PagesManageSystemPages = 'cms.pages.manage_system_pages';
    case PagesPreview = 'cms.pages.preview';
    case PagesSubmitReview = 'cms.pages.submit_review';
    case PagesApprove = 'cms.pages.approve';
    case PagesReject = 'cms.pages.reject';
    case PagesPublish = 'cms.pages.publish';
    case PagesSchedule = 'cms.pages.schedule';
    case PagesArchive = 'cms.pages.archive';
    case PagesRestore = 'cms.pages.restore';
    case BlocksViewDefinitions = 'cms.blocks.view_definitions';
    case BlocksManageDefinitions = 'cms.blocks.manage_definitions';
    case BlocksManageReusable = 'cms.blocks.manage_reusable';
    case BlocksUseApprovedOnly = 'cms.blocks.use_approved_only';
    case SeoManage = 'cms.seo.manage';
    case AuditView = 'cms.audit.view';
    case ApprovalsViewQueue = 'cms.approvals.view_queue';
    case ApprovalsAct = 'cms.approvals.act';
    case PreviewAccessShared = 'cms.preview.access_shared';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
