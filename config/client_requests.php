<?php

return [
    'upload_disk' => env('CLIENT_REQUESTS_UPLOAD_DISK', 'local'),

    'max_upload_kb' => 25 * 1024,

    'allowed_extensions' => [
        'pdf',
        'txt',
        'csv',
        'doc',
        'docx',
        'rtf',
        'odt',
        'jpg',
        'jpeg',
        'png',
        'webp',
        'mp3',
        'wav',
        'm4a',
        'ogg',
        'webm',
    ],

    'allowed_mime_types' => [
        'application/pdf',
        'text/plain',
        'text/csv',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/rtf',
        'application/vnd.oasis.opendocument.text',
        'image/jpeg',
        'image/png',
        'image/webp',
        'audio/mpeg',
        'audio/wav',
        'audio/x-wav',
        'audio/mp4',
        'audio/ogg',
        'audio/webm',
        'video/webm',
    ],

    'staff_notification_roles' => [
        'sales_manager',
        'super_admin',
    ],
];
