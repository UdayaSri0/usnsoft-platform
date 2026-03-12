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
use App\Modules\Products\Controllers\Admin\ProductCategoryController as AdminProductCategoryController;
use App\Modules\Products\Controllers\Admin\ProductController as AdminProductController;
use App\Modules\Products\Controllers\Admin\ProductReviewController as AdminProductReviewController;
use App\Modules\Products\Controllers\Admin\ProductTagController as AdminProductTagController;
use App\Modules\Products\Controllers\Site\ProductController as SiteProductController;
use App\Modules\Products\Controllers\Site\ProductDownloadController;
use App\Modules\Products\Controllers\Site\ProductPreviewController;
use App\Modules\Products\Controllers\Site\ProductReviewController as SiteProductReviewController;
use Illuminate\Http\Request;
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
        Route::get('/client-requests/new', function (Request $request) {
            return view('client-requests.create', [
                'user' => $request->user(),
            ]);
        })->middleware('permission:requests.create')->name('client-requests.create');
    });

    Route::get('/products/{product:slug_current}/downloads/{download}', ProductDownloadController::class)
        ->name('products.downloads.show');
    Route::post('/products/{product:slug_current}/reviews', [SiteProductReviewController::class, 'store'])
        ->middleware('verified')
        ->name('products.reviews.store');
});

Route::prefix('admin')
    ->middleware(['auth', 'active', 'admin.panel', 'internal.mfa'])
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/operations', function () {
            return view('admin.operations');
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

        Route::prefix('products')->name('products.')->group(function (): void {
            Route::get('/', [AdminProductController::class, 'index'])
                ->middleware('permission:products.view')
                ->name('index');
            Route::get('/create', [AdminProductController::class, 'create'])
                ->middleware('permission:products.create')
                ->name('create');
            Route::post('/', [AdminProductController::class, 'store'])
                ->middleware('permission:products.create')
                ->name('store');
            Route::get('/{product:id}', [AdminProductController::class, 'edit'])
                ->middleware('permission:products.view')
                ->name('edit');
            Route::put('/{product:id}', [AdminProductController::class, 'update'])
                ->middleware('permission:products.update')
                ->name('update');
            Route::post('/{product:id}/submit-review', [AdminProductController::class, 'submitForReview'])
                ->middleware('permission:products.submit_review')
                ->name('submit-review');
            Route::post('/versions/{version}/approve', [AdminProductController::class, 'approve'])
                ->middleware('permission:products.approve')
                ->name('versions.approve');
            Route::post('/versions/{version}/reject', [AdminProductController::class, 'reject'])
                ->middleware('permission:products.reject')
                ->name('versions.reject');
            Route::post('/versions/{version}/schedule', [AdminProductController::class, 'schedule'])
                ->middleware('permission:products.schedule')
                ->name('versions.schedule');
            Route::post('/versions/{version}/publish', [AdminProductController::class, 'publish'])
                ->middleware('permission:products.publish')
                ->name('versions.publish');
            Route::post('/versions/{version}/archive', [AdminProductController::class, 'archive'])
                ->middleware('permission:products.archive')
                ->name('versions.archive');
            Route::post('/versions/{version}/preview', [AdminProductController::class, 'preview'])
                ->middleware('permission:products.preview')
                ->name('versions.preview');

            Route::get('/categories/manage', [AdminProductCategoryController::class, 'index'])
                ->middleware('permission:products.categories.manage')
                ->name('categories.index');
            Route::post('/categories/manage', [AdminProductCategoryController::class, 'store'])
                ->middleware('permission:products.categories.manage')
                ->name('categories.store');
            Route::put('/categories/manage/{category}', [AdminProductCategoryController::class, 'update'])
                ->middleware('permission:products.categories.manage')
                ->name('categories.update');

            Route::get('/tags/manage', [AdminProductTagController::class, 'index'])
                ->middleware('permission:products.tags.manage')
                ->name('tags.index');
            Route::post('/tags/manage', [AdminProductTagController::class, 'store'])
                ->middleware('permission:products.tags.manage')
                ->name('tags.store');
            Route::put('/tags/manage/{tag}', [AdminProductTagController::class, 'update'])
                ->middleware('permission:products.tags.manage')
                ->name('tags.update');

            Route::get('/reviews/moderation', [AdminProductReviewController::class, 'index'])
                ->middleware('permission:products.reviews.moderate')
                ->name('reviews.index');
            Route::put('/reviews/moderation/{review}', [AdminProductReviewController::class, 'moderate'])
                ->middleware('permission:products.reviews.moderate')
                ->name('reviews.moderate');
        });
    });

require __DIR__.'/auth.php';

Route::get('/products', [SiteProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug_current}', [SiteProductController::class, 'show'])->name('products.show');

Route::get('/preview/pages/{version}', CmsPreviewController::class)
    ->name('cms.preview.show');
Route::get('/preview/products/{version}', ProductPreviewController::class)
    ->name('products.preview.show');

Route::get('/{path?}', CmsPageController::class)
    ->where('path', '.*')
    ->name('cms.page.show');
