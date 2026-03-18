<?php

return [
    'default' => env('USNSOFT_ANTI_SPAM_DRIVER', 'null'),

    'honeypot_field' => env('USNSOFT_ANTI_SPAM_HONEYPOT_FIELD', 'company_website'),

    'providers' => [
        'null' => [
            'driver' => 'null',
        ],
        'turnstile' => [
            'driver' => 'turnstile',
            'site_key' => env('TURNSTILE_SITE_KEY'),
            'secret_key' => env('TURNSTILE_SECRET_KEY'),
            'verify_url' => env('TURNSTILE_VERIFY_URL', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),
            'response_field' => env('TURNSTILE_RESPONSE_FIELD', 'cf-turnstile-response'),
        ],
    ],

    'forms' => [
        'blog_comment' => env('USNSOFT_ANTI_SPAM_BLOG_COMMENTS', true),
        'careers_application' => env('USNSOFT_ANTI_SPAM_CAREERS', true),
        'client_request' => env('USNSOFT_ANTI_SPAM_CLIENT_REQUESTS', true),
    ],
];
