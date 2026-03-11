<?php

namespace App\Modules\IdentityAccess\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Requests\InternalAccountStoreRequest;
use App\Modules\IdentityAccess\Services\InternalAccountProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InternalAccountController extends Controller
{
    public function create(): View
    {
        $this->authorize('createStaff', User::class);

        return view('admin.internal-accounts.create', [
            'roles' => Role::query()->where('is_internal', true)->orderBy('display_name')->get(),
        ]);
    }

    public function store(
        InternalAccountStoreRequest $request,
        InternalAccountProvisioningService $internalAccountProvisioningService,
    ): RedirectResponse {
        $this->authorize('createStaff', User::class);

        $role = Role::query()->findOrFail($request->integer('role_id'));

        $internalAccountProvisioningService->createInternalAccount(
            actor: $request->user(),
            attributes: $request->safe()->only(['name', 'email', 'password']),
            role: $role,
        );

        return redirect()->route('admin.internal-accounts.create')->with('status', 'internal-account-created');
    }
}
