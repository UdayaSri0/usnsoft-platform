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

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
