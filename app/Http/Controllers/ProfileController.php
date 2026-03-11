<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Modules\IdentityAccess\Models\AccountDeletionRequest;
use App\Modules\IdentityAccess\Services\AccountLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $this->authorize('view', $request->user());

        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('update', $user);

        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if ($request->hasFile('avatar')) {
            $disk = config('filesystems.default', 'local');

            if ($user->avatar_path) {
                Storage::disk($disk)->delete($user->avatar_path);
            }

            $user->avatar_path = $request->file('avatar')->store('avatars', $disk);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(
        Request $request,
        AccountLifecycleService $accountLifecycleService,
    ): RedirectResponse {
        $this->authorize('create', AccountDeletionRequest::class);

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $accountLifecycleService->requestDeletion(
            user: $request->user(),
            reason: $request->string('reason')->toString() ?: null,
        );

        return Redirect::route('profile.edit')->with('status', 'deletion-requested');
    }
}
