<?php

use App\Enums\CoreRole;

return [
    'enforce_internal_mfa' => env('USNSOFT_ENFORCE_INTERNAL_MFA', false),

    'suspicious_login_threshold' => (int) env('USNSOFT_SUSPICIOUS_LOGIN_THRESHOLD', 5),

    'session' => [
        'internal_idle_timeout_minutes' => (int) env('USNSOFT_INTERNAL_SESSION_IDLE_TIMEOUT', 30),
    ],

    'mfa' => [
        'issuer' => env('USNSOFT_MFA_ISSUER', env('APP_NAME', 'USNsoft')),
        'required_roles' => array_map(
            static fn (CoreRole $role): string => $role->value,
            CoreRole::internalRoles(),
        ),
        'challenge_timeout_minutes' => (int) env('USNSOFT_MFA_CHALLENGE_TIMEOUT_MINUTES', 720),
        'recovery_code_count' => (int) env('USNSOFT_MFA_RECOVERY_CODE_COUNT', 8),
    ],

    'headers' => [
        'enabled' => env('USNSOFT_SECURITY_HEADERS_ENABLED', true),
        'frame_options' => env('USNSOFT_SECURITY_FRAME_OPTIONS', 'SAMEORIGIN'),
        'referrer_policy' => env('USNSOFT_SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('USNSOFT_SECURITY_PERMISSIONS_POLICY', 'camera=(), geolocation=(), microphone=(), payment=(), usb=()'),
        'hsts_enabled' => env('USNSOFT_HSTS_ENABLED', false),
        'hsts_max_age' => (int) env('USNSOFT_HSTS_MAX_AGE', 31536000),
        'hsts_include_subdomains' => env('USNSOFT_HSTS_INCLUDE_SUBDOMAINS', true),
        'hsts_preload' => env('USNSOFT_HSTS_PRELOAD', false),
        'csp_report_only' => env('USNSOFT_CSP_REPORT_ONLY'),
    ],
];
