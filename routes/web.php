<?php

use App\Http\Controllers\Account\AccountDashboardController;
use App\Http\Controllers\Account\AccountDeletionRequestController;
use App\Http\Controllers\Account\DeviceHistoryController;
use App\Http\Controllers\Account\SessionHistoryController;
use App\Http\Controllers\ProfileController;
use App\Modules\IdentityAccess\Controllers\InternalAccountController;
use App\Modules\Pages\Controllers\Admin\ApprovalQueueController;
use App\Modules\Pages\Controllers\Admin\BlockDefinitionController;
use App\Modules\Pages\Controllers\Admin\PageController as AdminPageController;
use App\Modules\Pages\Controllers\Admin\ReusableBlockController;
use App\Modules\Pages\Controllers\Site\CmsPageController;
use App\Modules\Pages\Controllers\Site\CmsPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', CmsPageController::class)->name('home');

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

        Route::prefix('cms')->name('cms.')->group(function (): void {
            Route::get('/pages', [AdminPageController::class, 'index'])
                ->middleware('permission:cms.pages.view')
                ->name('pages.index');
            Route::get('/pages/create', [AdminPageController::class, 'create'])
                ->middleware('permission:cms.pages.create')
                ->name('pages.create');
            Route::post('/pages', [AdminPageController::class, 'store'])
                ->middleware('permission:cms.pages.create')
                ->name('pages.store');
            Route::get('/pages/{page}', [AdminPageController::class, 'edit'])
                ->middleware('permission:cms.pages.view')
                ->name('pages.edit');
            Route::put('/pages/{page}', [AdminPageController::class, 'update'])
                ->middleware('permission:cms.pages.update')
                ->name('pages.update');
            Route::post('/pages/{page}/submit-review', [AdminPageController::class, 'submitForReview'])
                ->middleware('permission:cms.pages.submit_review')
                ->name('pages.submit-review');
            Route::post('/versions/{version}/approve', [AdminPageController::class, 'approve'])
                ->middleware('permission:cms.pages.approve')
                ->name('versions.approve');
            Route::post('/versions/{version}/reject', [AdminPageController::class, 'reject'])
                ->middleware('permission:cms.pages.reject')
                ->name('versions.reject');
            Route::post('/versions/{version}/schedule', [AdminPageController::class, 'schedule'])
                ->middleware('permission:cms.pages.schedule')
                ->name('versions.schedule');
            Route::post('/versions/{version}/publish', [AdminPageController::class, 'publish'])
                ->middleware('permission:cms.pages.publish')
                ->name('versions.publish');
            Route::post('/versions/{version}/archive', [AdminPageController::class, 'archive'])
                ->middleware('permission:cms.pages.archive')
                ->name('versions.archive');
            Route::post('/versions/{version}/preview', [AdminPageController::class, 'preview'])
                ->middleware('permission:cms.pages.preview')
                ->name('versions.preview');

            Route::get('/approvals', ApprovalQueueController::class)
                ->middleware('permission:cms.approvals.view_queue')
                ->name('approvals.index');

            Route::get('/reusable-blocks', [ReusableBlockController::class, 'index'])
                ->middleware('permission:cms.blocks.manage_reusable')
                ->name('reusable-blocks.index');
            Route::get('/reusable-blocks/create', [ReusableBlockController::class, 'create'])
                ->middleware('permission:cms.blocks.manage_reusable')
                ->name('reusable-blocks.create');
            Route::post('/reusable-blocks', [ReusableBlockController::class, 'store'])
                ->middleware('permission:cms.blocks.manage_reusable')
                ->name('reusable-blocks.store');
            Route::get('/reusable-blocks/{reusableBlock}', [ReusableBlockController::class, 'edit'])
                ->middleware('permission:cms.blocks.manage_reusable')
                ->name('reusable-blocks.edit');
            Route::put('/reusable-blocks/{reusableBlock}', [ReusableBlockController::class, 'update'])
                ->middleware('permission:cms.blocks.manage_reusable')
                ->name('reusable-blocks.update');

            Route::get('/block-definitions', [BlockDefinitionController::class, 'index'])
                ->middleware('permission:cms.blocks.view_definitions')
                ->name('block-definitions.index');
            Route::put('/block-definitions/{blockDefinition}', [BlockDefinitionController::class, 'update'])
                ->middleware('permission:cms.blocks.manage_definitions')
                ->name('block-definitions.update');
        });
    });

require __DIR__.'/auth.php';

Route::get('/preview/pages/{version}', CmsPreviewController::class)
    ->name('cms.preview.show');

Route::get('/{path?}', CmsPageController::class)
    ->where('path', '.*')
    ->name('cms.page.show');
