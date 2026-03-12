<?php

return [
    'upload_disk' => env('USNSOFT_CAREERS_UPLOAD_DISK', 'local'),

    'max_upload_kb' => (int) env('USNSOFT_CAREERS_MAX_UPLOAD_KB', 10240),

    'allowed_extensions' => [
        'pdf',
        'doc',
        'docx',
    ],

    'allowed_mime_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],
];
