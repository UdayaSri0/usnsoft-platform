<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Modules\AuditSecurity\Models\UserSessionHistory;
use App\Modules\AuditSecurity\Services\LoginSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SessionHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        Gate::authorize('viewOwnSessionHistory', [UserSessionHistory::class, $user]);

        $history = $user
            ->sessionHistories()
            ->latest('logged_in_at')
            ->paginate(20);

        return view('account.sessions.index', [
            'user' => $user,
            'history' => $history,
            'currentSessionId' => $request->session()->getId(),
        ]);
    }

    public function destroyOtherSessions(
        Request $request,
        LoginSecurityService $loginSecurityService,
    ): RedirectResponse {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $sessionId = $request->session()->getId();
        $invalidated = $loginSecurityService->invalidateOtherSessions($request->user(), $sessionId);

        Auth::logoutOtherDevices($request->string('password')->toString());

        return redirect()
            ->route('account.sessions.index')
            ->with('status', $invalidated > 0 ? 'other-sessions-invalidated' : 'no-other-sessions');
    }
}
