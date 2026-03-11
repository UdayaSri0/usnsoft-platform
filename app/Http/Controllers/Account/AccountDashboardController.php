<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('view', $request->user());

        return view('account.dashboard', [
            'user' => $request->user(),
        ]);
    }
}
