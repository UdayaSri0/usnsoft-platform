<?php

return [
    'naming_convention' => 'resource.action',

    'examples' => [
        'profile.update',
        'users.assignRoles',
        'staff.create',
        'downloads.protected.access',
        'requests.create',
        'cms.pages.submit_review',
        'cms.pages.publish',
    ],

    'admin_route_protection' => [
        'internal' => ['auth', 'verified', 'active', 'internal', 'permission:admin.access'],
        'super_admin' => ['auth', 'verified', 'active', 'superadmin'],
    ],
];
