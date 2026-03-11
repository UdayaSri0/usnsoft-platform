<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Modules\AuditSecurity\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DeviceHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        Gate::authorize('viewOwnDeviceHistory', [UserDevice::class, $user]);

        $devices = $user->devices()
            ->latest('last_seen_at')
            ->paginate(20);

        return view('account.devices.index', [
            'devices' => $devices,
            'currentFingerprint' => hash('sha256', mb_strtolower((string) $request->userAgent()).'|'.$request->ip()),
        ]);
    }
}
