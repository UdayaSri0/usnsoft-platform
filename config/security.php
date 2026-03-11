<?php

return [
    'enforce_internal_mfa' => env('USNSOFT_ENFORCE_INTERNAL_MFA', false),
    'suspicious_login_threshold' => env('USNSOFT_SUSPICIOUS_LOGIN_THRESHOLD', 5),
];
