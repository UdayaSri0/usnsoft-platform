<?php

namespace App\Modules\IdentityAccess\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\AuditSecurity\Models\AuditLog;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Requests\Admin\ManagedAccountStoreRequest;
use App\Modules\IdentityAccess\Requests\Admin\ManagedAccountUpdateRequest;
use App\Modules\IdentityAccess\Services\Admin\AccountManagementService;
use App\Modules\IdentityAccess\Services\Admin\AccountRoleScopeService;
use App\Modules\IdentityAccess\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly AccountManagementService $accountManagementService,
        private readonly AccountRoleScopeService $roleScopeService,
        private readonly MfaService $mfaService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $q = $request->string('q')->toString();
        $role = $request->string('role')->toString();
        $status = $request->string('status')->toString();
        $verified = $request->string('verified')->toString();
        $internal = $request->string('internal')->toString();

        $accounts = User::query()
            ->with('roles')
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($searchQuery) use ($q): void {
                    $searchQuery
                        ->where('name', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%')
                        ->orWhere('phone', 'like', '%'.$q.'%');
                });
            })
            ->when($role !== '', fn ($query) => $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', $role)))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($verified === 'verified', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when($verified === 'unverified', fn ($query) => $query->whereNull('email_verified_at'))
            ->when($internal === '1', fn ($query) => $query->where('is_internal', true))
            ->when($internal === '0', fn ($query) => $query->where('is_internal', false))
            ->orderByDesc('is_internal')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.accounts.index', [
            'accounts' => $accounts,
            'filters' => compact('q', 'role', 'status', 'verified', 'internal'),
            'roles' => Role::query()->orderBy('is_internal')->orderBy('display_name')->get(),
            'creatableRoles' => $this->roleScopeService->creatableRoles($request->user()),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $roles = $this->roleScopeService->creatableRoles($request->user());
        abort_if($roles->isEmpty(), 403);

        return view('admin.accounts.create', [
            'roles' => $roles,
        ]);
    }

    public function store(ManagedAccountStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $role = Role::query()->findOrFail($request->integer('role_id'));

        $account = $this->accountManagementService->create(
            actor: $request->user(),
            attributes: $request->safe()->only(['name', 'email', 'phone', 'password']),
            role: $role,
        );

        return redirect()
            ->route('admin.accounts.edit', ['user' => $account->getKey()])
            ->with('status', 'managed-account-created');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('manage', $user);

        $user->load('roles');

        return view('admin.accounts.edit', [
            'account' => $user,
            'roles' => $this->roleScopeService->editableRoles($request->user(), $user),
            'activeMfaMethod' => $this->mfaService->activeMethod($user),
            'auditTrail' => AuditLog::query()
                ->where('auditable_type', $user->getMorphClass())
                ->where('auditable_id', $user->getKey())
                ->latest('created_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function update(ManagedAccountUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manage', $user);

        $roleId = $request->integer('role_id');
        $role = $roleId > 0 ? Role::query()->findOrFail($roleId) : null;

        $this->accountManagementService->update(
            actor: $request->user(),
            target: $user,
            attributes: $request->safe()->only(['name', 'email', 'phone']),
            role: $role,
        );

        return back()->with('status', 'managed-account-updated');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('deactivateManaged', $user);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->accountManagementService->deactivate(
            actor: $request->user(),
            target: $user,
            reason: $validated['reason'] ?? null,
        );

        return back()->with('status', 'managed-account-deactivated');
    }

    public function reactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('deactivateManaged', $user);

        $this->accountManagementService->reactivate(
            actor: $request->user(),
            target: $user,
        );

        return back()->with('status', 'managed-account-reactivated');
    }

    public function sendPasswordResetLink(Request $request, User $user): RedirectResponse
    {
        $this->authorize('initiatePasswordReset', $user);

        $status = $this->accountManagementService->sendPasswordResetLink(
            actor: $request->user(),
            target: $user,
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors([
                'password_reset' => __($status),
            ]);
        }

        return back()->with('status', 'managed-account-password-reset-sent');
    }

    public function disableMfa(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manageMfa', $user);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->mfaService->disable(
            actor: $request->user(),
            target: $user,
            reason: $validated['reason'] ?? 'staff-enforced disable',
        );

        return back()->with('status', 'managed-account-mfa-disabled');
    }
}
