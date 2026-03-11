<?php

namespace App\Modules\IdentityAccess\Enums;

enum PermissionName: string
{
    case ProfileView = 'profile.view';
    case ProfileUpdate = 'profile.update';
    case AccountRequestDeletion = 'account.requestDeletion';
    case SecuritySessionsViewOwn = 'security.sessions.viewOwn';
    case SecurityDevicesViewOwn = 'security.devices.viewOwn';
    case SecurityLogsView = 'security.logs.view';
    case SecurityEventsView = 'security.events.view';
    case UsersViewAny = 'users.viewAny';
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDeactivate = 'users.deactivate';
    case UsersRestore = 'users.restore';
    case UsersAssignRoles = 'users.assignRoles';
    case UsersAssignPermissions = 'users.assignPermissions';
    case StaffCreate = 'staff.create';
    case StaffViewAny = 'staff.viewAny';
    case StaffUpdate = 'staff.update';
    case StaffDeactivate = 'staff.deactivate';
    case RolesView = 'roles.view';
    case RolesUpdate = 'roles.update';
    case PermissionsView = 'permissions.view';
    case PermissionsUpdate = 'permissions.update';
    case AdminAccess = 'admin.access';
    case SuperAdminAccess = 'superadmin.access';
    case ProtectedDownloadsAccess = 'downloads.protected.access';
    case RequestsCreate = 'requests.create';
    case RequestsViewOwn = 'requests.viewOwn';
    case RequestsViewAny = 'requests.viewAny';
    case RequestsUpdateStatus = 'requests.updateStatus';
    case RequestsCommentInternal = 'requests.commentInternal';
    case RequestsCommentPublic = 'requests.commentPublic';
    case CmsPagesView = 'cms.pages.view';
    case CmsPagesCreate = 'cms.pages.create';
    case CmsPagesUpdate = 'cms.pages.update';
    case CmsPagesDelete = 'cms.pages.delete';
    case CmsPagesCompose = 'cms.pages.compose';
    case CmsPagesReorderBlocks = 'cms.pages.reorder_blocks';
    case CmsPagesUseReusableBlocks = 'cms.pages.use_reusable_blocks';
    case CmsPagesUseAdvancedBlocks = 'cms.pages.use_advanced_blocks';
    case CmsPagesManageHomepage = 'cms.pages.manage_homepage';
    case CmsPagesManageSystemPages = 'cms.pages.manage_system_pages';
    case CmsPagesPreview = 'cms.pages.preview';
    case CmsPagesSubmitReview = 'cms.pages.submit_review';
    case CmsPagesApprove = 'cms.pages.approve';
    case CmsPagesReject = 'cms.pages.reject';
    case CmsPagesPublish = 'cms.pages.publish';
    case CmsPagesSchedule = 'cms.pages.schedule';
    case CmsPagesArchive = 'cms.pages.archive';
    case CmsPagesRestore = 'cms.pages.restore';
    case CmsBlocksViewDefinitions = 'cms.blocks.view_definitions';
    case CmsBlocksManageDefinitions = 'cms.blocks.manage_definitions';
    case CmsBlocksManageReusable = 'cms.blocks.manage_reusable';
    case CmsBlocksUseApprovedOnly = 'cms.blocks.use_approved_only';
    case CmsSeoManage = 'cms.seo.manage';
    case CmsAuditView = 'cms.audit.view';
    case CmsApprovalsViewQueue = 'cms.approvals.view_queue';
    case CmsApprovalsAct = 'cms.approvals.act';
    case CmsPreviewAccessShared = 'cms.preview.access_shared';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
