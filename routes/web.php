<?php

use App\Http\Controllers\Account\AccountDashboardController;
use App\Http\Controllers\Account\AccountDeletionRequestController;
use App\Http\Controllers\Account\DeviceHistoryController;
use App\Http\Controllers\Account\SessionHistoryController;
use App\Http\Controllers\ProfileController;
use App\Modules\IdentityAccess\Controllers\InternalAccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'active', 'session.track'])->group(function (): void {
    Route::get('/dashboard', AccountDashboardController::class)
        ->middleware('verified')
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('account')->name('account.')->group(function (): void {
        Route::get('/', AccountDashboardController::class)->middleware('verified')->name('dashboard');
        Route::get('/sessions', [SessionHistoryController::class, 'index'])
            ->middleware('permission:security.sessions.viewOwn')
            ->name('sessions.index');
        Route::post('/sessions/logout-others', [SessionHistoryController::class, 'destroyOtherSessions'])
            ->middleware('permission:security.sessions.viewOwn')
            ->name('sessions.destroy-others');
        Route::get('/devices', [DeviceHistoryController::class, 'index'])
            ->middleware('permission:security.devices.viewOwn')
            ->name('devices.index');
        Route::post('/deletion-request', [AccountDeletionRequestController::class, 'store'])
            ->middleware('permission:account.requestDeletion')
            ->name('deletion-request.store');
    });

    Route::middleware(['verified.feature'])->group(function (): void {
        Route::get('/client-requests/new', function () {
            return response('Client request form placeholder', 200);
        })->middleware('permission:requests.create')->name('client-requests.create');

        Route::get('/products/{product}/download', function () {
            return response('Protected download placeholder', 200);
        })->middleware('permission:downloads.protected.access')->name('products.download');
    });
});

Route::prefix('admin')
    ->middleware(['auth', 'active', 'admin.panel', 'internal.mfa'])
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/operations', function () {
            return response('Admin operations placeholder', 200);
        })->middleware('admin')->name('operations');

        Route::get('/internal-accounts/create', [InternalAccountController::class, 'create'])
            ->middleware('superadmin')
            ->name('internal-accounts.create');
        Route::post('/internal-accounts', [InternalAccountController::class, 'store'])
            ->middleware('superadmin')
            ->name('internal-accounts.store');
    });

require __DIR__.'/auth.php';
