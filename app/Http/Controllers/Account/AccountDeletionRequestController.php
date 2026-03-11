<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestAccountDeletionFormRequest;
use App\Modules\IdentityAccess\Models\AccountDeletionRequest;
use App\Modules\IdentityAccess\Services\AccountLifecycleService;
use Illuminate\Http\RedirectResponse;

class AccountDeletionRequestController extends Controller
{
    public function store(
        RequestAccountDeletionFormRequest $request,
        AccountLifecycleService $accountLifecycleService,
    ): RedirectResponse {
        $this->authorize('create', AccountDeletionRequest::class);

        $accountLifecycleService->requestDeletion(
            user: $request->user(),
            reason: $request->string('reason')->toString() ?: null,
        );

        return redirect()->route('profile.edit')->with('status', 'deletion-requested');
    }
}
