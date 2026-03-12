<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Modules\ClientRequests\Models\ProjectRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('view', $request->user());

        return view('account.dashboard', [
            'user' => $request->user(),
            'projectRequestCount' => ProjectRequest::query()
                ->ownedBy($request->user())
                ->count(),
            'recentProjectRequests' => ProjectRequest::query()
                ->ownedBy($request->user())
                ->with('currentStatus')
                ->orderByDesc('submitted_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
