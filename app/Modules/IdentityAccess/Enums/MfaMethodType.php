<?php

namespace App\Modules\IdentityAccess\Enums;

enum MfaMethodType: string
{
    case Totp = 'totp';
}
