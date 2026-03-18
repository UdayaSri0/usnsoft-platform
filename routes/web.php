<?php

use App\Http\Controllers\Account\AccountDashboardController;
use App\Http\Controllers\Account\AccountDeletionRequestController;
use App\Http\Controllers\Account\DeviceHistoryController;
use App\Http\Controllers\Account\SessionHistoryController;
use App\Http\Controllers\ProfileController;
use App\Modules\Blog\Controllers\Admin\BlogCategoryController as AdminBlogCategoryController;
use App\Modules\Blog\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Modules\Blog\Controllers\Admin\BlogTagController as AdminBlogTagController;
use App\Modules\Blog\Controllers\Site\BlogController as SiteBlogController;
use App\Modules\Careers\Controllers\Admin\JobApplicationController as AdminJobApplicationController;
use App\Modules\Careers\Controllers\Admin\JobController as AdminJobController;
use App\Modules\Careers\Controllers\Site\CareerController as SiteCareerController;
use App\Modules\ClientRequests\Controllers\Account\ProjectRequestAttachmentController as AccountProjectRequestAttachmentController;
use App\Modules\ClientRequests\Controllers\Account\ProjectRequestCommentController as AccountProjectRequestCommentController;
use App\Modules\ClientRequests\Controllers\Account\ProjectRequestController as AccountProjectRequestController;
use App\Modules\ClientRequests\Controllers\Admin\ProjectRequestCommentController as AdminProjectRequestCommentController;
use App\Modules\ClientRequests\Controllers\Admin\ProjectRequestController as AdminProjectRequestController;
use App\Modules\ClientRequests\Controllers\Admin\ProjectRequestStatusController as AdminProjectRequestStatusController;
use App\Modules\Comments\Controllers\Admin\CommentController as AdminCommentController;
use App\Modules\Comments\Controllers\Site\BlogCommentController;
use App\Modules\Faq\Controllers\Admin\FaqCategoryController as AdminFaqCategoryController;
use App\Modules\Faq\Controllers\Admin\FaqController as AdminFaqController;
use App\Modules\Faq\Controllers\Site\FaqController as SiteFaqController;
use App\Modules\IdentityAccess\Controllers\Admin\AccountController as AdminAccountController;
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
use App\Modules\Showcase\Controllers\Admin\AchievementController as AdminAchievementController;
use App\Modules\Showcase\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Modules\Showcase\Controllers\Admin\TeamMemberController as AdminTeamMemberController;
use App\Modules\Showcase\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Modules\Showcase\Controllers\Admin\TimelineEntryController as AdminTimelineEntryController;
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
        Route::get('/client-requests/new', [AccountProjectRequestController::class, 'create'])
            ->middleware('permission:requests.create')
            ->name('client-requests.create');
        Route::post('/client-requests', [AccountProjectRequestController::class, 'store'])
            ->middleware('permission:requests.create')
            ->name('client-requests.store');
        Route::post('/client-requests/{projectRequest:uuid}/comments', [AccountProjectRequestCommentController::class, 'store'])
            ->middleware('permission:requests.commentPublic')
            ->name('client-requests.comments.store');
    });

    Route::get('/client-requests', [AccountProjectRequestController::class, 'index'])
        ->middleware('permission:requests.viewOwn')
        ->name('client-requests.index');
    Route::get('/client-requests/{projectRequest:uuid}', [AccountProjectRequestController::class, 'show'])
        ->middleware('permission:requests.viewOwn')
        ->name('client-requests.show');
    Route::get('/client-requests/{projectRequest:uuid}/attachments/{attachment:uuid}', [AccountProjectRequestAttachmentController::class, 'showForRequester'])
        ->middleware('permission:requests.files.download')
        ->name('client-requests.attachments.show');

    Route::get('/products/{product:slug_current}/downloads/{download}', ProductDownloadController::class)
        ->name('products.downloads.show');
    Route::post('/products/{product:slug_current}/reviews', [SiteProductReviewController::class, 'store'])
        ->middleware('verified')
        ->name('products.reviews.store');
    Route::post('/blog/{post:slug}/comments', [BlogCommentController::class, 'store'])
        ->middleware(['verified', 'permission:comments.create'])
        ->name('blog.comments.store');
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

        Route::prefix('accounts')->name('accounts.')->group(function (): void {
            Route::get('/', [AdminAccountController::class, 'index'])
                ->middleware('permission:users.viewAny')
                ->name('index');
            Route::get('/create', [AdminAccountController::class, 'create'])
                ->middleware('permission:users.create')
                ->name('create');
            Route::post('/', [AdminAccountController::class, 'store'])
                ->middleware('permission:users.create')
                ->name('store');
            Route::get('/{user}', [AdminAccountController::class, 'edit'])
                ->middleware('permission:users.update')
                ->name('edit');
            Route::put('/{user}', [AdminAccountController::class, 'update'])
                ->middleware('permission:users.update')
                ->name('update');
            Route::post('/{user}/deactivate', [AdminAccountController::class, 'deactivate'])
                ->middleware('permission:users.deactivate')
                ->name('deactivate');
            Route::post('/{user}/reactivate', [AdminAccountController::class, 'reactivate'])
                ->middleware('permission:users.deactivate')
                ->name('reactivate');
            Route::post('/{user}/password-reset-link', [AdminAccountController::class, 'sendPasswordResetLink'])
                ->middleware('permission:users.passwordReset')
                ->name('password-reset-link');
        });

        Route::prefix('comments')->name('comments.')->group(function (): void {
            Route::get('/', [AdminCommentController::class, 'index'])
                ->middleware('permission:comments.viewAny')
                ->name('index');
            Route::put('/{comment}', [AdminCommentController::class, 'moderate'])
                ->middleware('permission:comments.moderate')
                ->name('moderate');
        });

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

        Route::prefix('blog')->name('blog.')->group(function (): void {
            Route::get('/', [AdminBlogPostController::class, 'index'])
                ->middleware('permission:blog.view')
                ->name('index');
            Route::get('/create', [AdminBlogPostController::class, 'create'])
                ->middleware('permission:blog.create')
                ->name('create');
            Route::post('/', [AdminBlogPostController::class, 'store'])
                ->middleware('permission:blog.create')
                ->name('store');
            Route::get('/categories/manage', [AdminBlogCategoryController::class, 'index'])
                ->middleware('permission:blog.categories.manage')
                ->name('categories.index');
            Route::post('/categories/manage', [AdminBlogCategoryController::class, 'store'])
                ->middleware('permission:blog.categories.manage')
                ->name('categories.store');
            Route::put('/categories/manage/{category}', [AdminBlogCategoryController::class, 'update'])
                ->middleware('permission:blog.categories.manage')
                ->name('categories.update');

            Route::get('/tags/manage', [AdminBlogTagController::class, 'index'])
                ->middleware('permission:blog.tags.manage')
                ->name('tags.index');
            Route::post('/tags/manage', [AdminBlogTagController::class, 'store'])
                ->middleware('permission:blog.tags.manage')
                ->name('tags.store');
            Route::put('/tags/manage/{tag}', [AdminBlogTagController::class, 'update'])
                ->middleware('permission:blog.tags.manage')
                ->name('tags.update');
            Route::get('/{post:id}', [AdminBlogPostController::class, 'edit'])
                ->middleware('permission:blog.view')
                ->name('edit');
            Route::put('/{post:id}', [AdminBlogPostController::class, 'update'])
                ->middleware('permission:blog.update')
                ->name('update');
            Route::post('/{post:id}/submit-review', [AdminBlogPostController::class, 'submitForReview'])
                ->middleware('permission:blog.submit_review')
                ->name('submit-review');
            Route::post('/{post:id}/approve', [AdminBlogPostController::class, 'approve'])
                ->middleware('permission:blog.approve')
                ->name('versions.approve');
            Route::post('/{post:id}/reject', [AdminBlogPostController::class, 'reject'])
                ->middleware('permission:blog.reject')
                ->name('versions.reject');
            Route::post('/{post:id}/schedule', [AdminBlogPostController::class, 'schedule'])
                ->middleware('permission:blog.schedule')
                ->name('versions.schedule');
            Route::post('/{post:id}/publish', [AdminBlogPostController::class, 'publish'])
                ->middleware('permission:blog.publish')
                ->name('versions.publish');
            Route::post('/{post:id}/archive', [AdminBlogPostController::class, 'archive'])
                ->middleware('permission:blog.archive')
                ->name('versions.archive');
        });

        Route::prefix('faq')->name('faq.')->group(function (): void {
            Route::get('/', [AdminFaqController::class, 'index'])
                ->middleware('permission:faq.view')
                ->name('index');
            Route::get('/create', [AdminFaqController::class, 'create'])
                ->middleware('permission:faq.create')
                ->name('create');
            Route::post('/', [AdminFaqController::class, 'store'])
                ->middleware('permission:faq.create')
                ->name('store');
            Route::get('/categories/manage', [AdminFaqCategoryController::class, 'index'])
                ->middleware('permission:faq.categories.manage')
                ->name('categories.index');
            Route::post('/categories/manage', [AdminFaqCategoryController::class, 'store'])
                ->middleware('permission:faq.categories.manage')
                ->name('categories.store');
            Route::put('/categories/manage/{category}', [AdminFaqCategoryController::class, 'update'])
                ->middleware('permission:faq.categories.manage')
                ->name('categories.update');
            Route::get('/{faq}', [AdminFaqController::class, 'edit'])
                ->middleware('permission:faq.view')
                ->name('edit');
            Route::put('/{faq}', [AdminFaqController::class, 'update'])
                ->middleware('permission:faq.update')
                ->name('update');
            Route::post('/{faq}/submit-review', [AdminFaqController::class, 'submitForReview'])
                ->middleware('permission:faq.submit_review')
                ->name('submit-review');
            Route::post('/{faq}/approve', [AdminFaqController::class, 'approve'])
                ->middleware('permission:faq.approve')
                ->name('versions.approve');
            Route::post('/{faq}/reject', [AdminFaqController::class, 'reject'])
                ->middleware('permission:faq.reject')
                ->name('versions.reject');
            Route::post('/{faq}/schedule', [AdminFaqController::class, 'schedule'])
                ->middleware('permission:faq.schedule')
                ->name('versions.schedule');
            Route::post('/{faq}/publish', [AdminFaqController::class, 'publish'])
                ->middleware('permission:faq.publish')
                ->name('versions.publish');
            Route::post('/{faq}/archive', [AdminFaqController::class, 'archive'])
                ->middleware('permission:faq.archive')
                ->name('versions.archive');
        });

        Route::prefix('careers')->name('careers.')->group(function (): void {
            Route::get('/', [AdminJobController::class, 'index'])
                ->middleware('permission:careers.view')
                ->name('index');
            Route::get('/create', [AdminJobController::class, 'create'])
                ->middleware('permission:careers.create')
                ->name('create');
            Route::post('/', [AdminJobController::class, 'store'])
                ->middleware('permission:careers.create')
                ->name('store');
            Route::get('/applications', [AdminJobApplicationController::class, 'index'])
                ->middleware('permission:careers.applications.view')
                ->name('applications.index');
            Route::get('/applications/{application}', [AdminJobApplicationController::class, 'show'])
                ->middleware('permission:careers.applications.view')
                ->name('applications.show');
            Route::put('/applications/{application}/status', [AdminJobApplicationController::class, 'updateStatus'])
                ->middleware('permission:careers.applications.update')
                ->name('applications.status.update');
            Route::post('/applications/{application}/notes', [AdminJobApplicationController::class, 'addNote'])
                ->middleware('permission:careers.applications.notes.manage')
                ->name('applications.notes.store');
            Route::get('/applications/files/{file}', [AdminJobApplicationController::class, 'download'])
                ->middleware('permission:careers.applications.files.view')
                ->name('applications.files.download');
            Route::get('/{job:id}', [AdminJobController::class, 'edit'])
                ->middleware('permission:careers.view')
                ->name('edit');
            Route::put('/{job:id}', [AdminJobController::class, 'update'])
                ->middleware('permission:careers.update')
                ->name('update');
            Route::post('/{job:id}/submit-review', [AdminJobController::class, 'submitForReview'])
                ->middleware('permission:careers.submit_review')
                ->name('submit-review');
            Route::post('/{job:id}/approve', [AdminJobController::class, 'approve'])
                ->middleware('permission:careers.approve')
                ->name('versions.approve');
            Route::post('/{job:id}/reject', [AdminJobController::class, 'reject'])
                ->middleware('permission:careers.reject')
                ->name('versions.reject');
            Route::post('/{job:id}/schedule', [AdminJobController::class, 'schedule'])
                ->middleware('permission:careers.schedule')
                ->name('versions.schedule');
            Route::post('/{job:id}/publish', [AdminJobController::class, 'publish'])
                ->middleware('permission:careers.publish')
                ->name('versions.publish');
            Route::post('/{job:id}/archive', [AdminJobController::class, 'archive'])
                ->middleware('permission:careers.archive')
                ->name('versions.archive');
        });

        Route::prefix('showcase')->name('showcase.')->group(function (): void {
            Route::prefix('testimonials')->name('testimonials.')->group(function (): void {
                Route::get('/', [AdminTestimonialController::class, 'index'])->middleware('permission:showcase.testimonials.manage')->name('index');
                Route::get('/create', [AdminTestimonialController::class, 'create'])->middleware('permission:showcase.testimonials.manage')->name('create');
                Route::post('/', [AdminTestimonialController::class, 'store'])->middleware('permission:showcase.testimonials.manage')->name('store');
                Route::get('/{item}', [AdminTestimonialController::class, 'edit'])->middleware('permission:showcase.testimonials.manage')->name('edit');
                Route::put('/{item}', [AdminTestimonialController::class, 'update'])->middleware('permission:showcase.testimonials.manage')->name('update');
                Route::post('/{item}/submit-review', [AdminTestimonialController::class, 'submitForReview'])->middleware('permission:showcase.submit_review')->name('submit-review');
                Route::post('/{item}/approve', [AdminTestimonialController::class, 'approve'])->middleware('permission:showcase.approve')->name('versions.approve');
                Route::post('/{item}/reject', [AdminTestimonialController::class, 'reject'])->middleware('permission:showcase.reject')->name('versions.reject');
                Route::post('/{item}/schedule', [AdminTestimonialController::class, 'schedule'])->middleware('permission:showcase.schedule')->name('versions.schedule');
                Route::post('/{item}/publish', [AdminTestimonialController::class, 'publish'])->middleware('permission:showcase.publish')->name('versions.publish');
                Route::post('/{item}/archive', [AdminTestimonialController::class, 'archive'])->middleware('permission:showcase.archive')->name('versions.archive');
            });

            Route::prefix('partners')->name('partners.')->group(function (): void {
                Route::get('/', [AdminPartnerController::class, 'index'])->middleware('permission:showcase.partners.manage')->name('index');
                Route::get('/create', [AdminPartnerController::class, 'create'])->middleware('permission:showcase.partners.manage')->name('create');
                Route::post('/', [AdminPartnerController::class, 'store'])->middleware('permission:showcase.partners.manage')->name('store');
                Route::get('/{item}', [AdminPartnerController::class, 'edit'])->middleware('permission:showcase.partners.manage')->name('edit');
                Route::put('/{item}', [AdminPartnerController::class, 'update'])->middleware('permission:showcase.partners.manage')->name('update');
                Route::post('/{item}/submit-review', [AdminPartnerController::class, 'submitForReview'])->middleware('permission:showcase.submit_review')->name('submit-review');
                Route::post('/{item}/approve', [AdminPartnerController::class, 'approve'])->middleware('permission:showcase.approve')->name('versions.approve');
                Route::post('/{item}/reject', [AdminPartnerController::class, 'reject'])->middleware('permission:showcase.reject')->name('versions.reject');
                Route::post('/{item}/schedule', [AdminPartnerController::class, 'schedule'])->middleware('permission:showcase.schedule')->name('versions.schedule');
                Route::post('/{item}/publish', [AdminPartnerController::class, 'publish'])->middleware('permission:showcase.publish')->name('versions.publish');
                Route::post('/{item}/archive', [AdminPartnerController::class, 'archive'])->middleware('permission:showcase.archive')->name('versions.archive');
            });

            Route::prefix('team')->name('team.')->group(function (): void {
                Route::get('/', [AdminTeamMemberController::class, 'index'])->middleware('permission:showcase.team.manage')->name('index');
                Route::get('/create', [AdminTeamMemberController::class, 'create'])->middleware('permission:showcase.team.manage')->name('create');
                Route::post('/', [AdminTeamMemberController::class, 'store'])->middleware('permission:showcase.team.manage')->name('store');
                Route::get('/{item}', [AdminTeamMemberController::class, 'edit'])->middleware('permission:showcase.team.manage')->name('edit');
                Route::put('/{item}', [AdminTeamMemberController::class, 'update'])->middleware('permission:showcase.team.manage')->name('update');
                Route::post('/{item}/submit-review', [AdminTeamMemberController::class, 'submitForReview'])->middleware('permission:showcase.submit_review')->name('submit-review');
                Route::post('/{item}/approve', [AdminTeamMemberController::class, 'approve'])->middleware('permission:showcase.approve')->name('versions.approve');
                Route::post('/{item}/reject', [AdminTeamMemberController::class, 'reject'])->middleware('permission:showcase.reject')->name('versions.reject');
                Route::post('/{item}/schedule', [AdminTeamMemberController::class, 'schedule'])->middleware('permission:showcase.schedule')->name('versions.schedule');
                Route::post('/{item}/publish', [AdminTeamMemberController::class, 'publish'])->middleware('permission:showcase.publish')->name('versions.publish');
                Route::post('/{item}/archive', [AdminTeamMemberController::class, 'archive'])->middleware('permission:showcase.archive')->name('versions.archive');
            });

            Route::prefix('timeline')->name('timeline.')->group(function (): void {
                Route::get('/', [AdminTimelineEntryController::class, 'index'])->middleware('permission:showcase.timeline.manage')->name('index');
                Route::get('/create', [AdminTimelineEntryController::class, 'create'])->middleware('permission:showcase.timeline.manage')->name('create');
                Route::post('/', [AdminTimelineEntryController::class, 'store'])->middleware('permission:showcase.timeline.manage')->name('store');
                Route::get('/{item}', [AdminTimelineEntryController::class, 'edit'])->middleware('permission:showcase.timeline.manage')->name('edit');
                Route::put('/{item}', [AdminTimelineEntryController::class, 'update'])->middleware('permission:showcase.timeline.manage')->name('update');
                Route::post('/{item}/submit-review', [AdminTimelineEntryController::class, 'submitForReview'])->middleware('permission:showcase.submit_review')->name('submit-review');
                Route::post('/{item}/approve', [AdminTimelineEntryController::class, 'approve'])->middleware('permission:showcase.approve')->name('versions.approve');
                Route::post('/{item}/reject', [AdminTimelineEntryController::class, 'reject'])->middleware('permission:showcase.reject')->name('versions.reject');
                Route::post('/{item}/schedule', [AdminTimelineEntryController::class, 'schedule'])->middleware('permission:showcase.schedule')->name('versions.schedule');
                Route::post('/{item}/publish', [AdminTimelineEntryController::class, 'publish'])->middleware('permission:showcase.publish')->name('versions.publish');
                Route::post('/{item}/archive', [AdminTimelineEntryController::class, 'archive'])->middleware('permission:showcase.archive')->name('versions.archive');
            });

            Route::prefix('achievements')->name('achievements.')->group(function (): void {
                Route::get('/', [AdminAchievementController::class, 'index'])->middleware('permission:showcase.achievements.manage')->name('index');
                Route::get('/create', [AdminAchievementController::class, 'create'])->middleware('permission:showcase.achievements.manage')->name('create');
                Route::post('/', [AdminAchievementController::class, 'store'])->middleware('permission:showcase.achievements.manage')->name('store');
                Route::get('/{item}', [AdminAchievementController::class, 'edit'])->middleware('permission:showcase.achievements.manage')->name('edit');
                Route::put('/{item}', [AdminAchievementController::class, 'update'])->middleware('permission:showcase.achievements.manage')->name('update');
                Route::post('/{item}/submit-review', [AdminAchievementController::class, 'submitForReview'])->middleware('permission:showcase.submit_review')->name('submit-review');
                Route::post('/{item}/approve', [AdminAchievementController::class, 'approve'])->middleware('permission:showcase.approve')->name('versions.approve');
                Route::post('/{item}/reject', [AdminAchievementController::class, 'reject'])->middleware('permission:showcase.reject')->name('versions.reject');
                Route::post('/{item}/schedule', [AdminAchievementController::class, 'schedule'])->middleware('permission:showcase.schedule')->name('versions.schedule');
                Route::post('/{item}/publish', [AdminAchievementController::class, 'publish'])->middleware('permission:showcase.publish')->name('versions.publish');
                Route::post('/{item}/archive', [AdminAchievementController::class, 'archive'])->middleware('permission:showcase.archive')->name('versions.archive');
            });
        });

        Route::prefix('client-requests')->name('client-requests.')->group(function (): void {
            Route::get('/statuses/manage', [AdminProjectRequestStatusController::class, 'index'])
                ->middleware('permission:requests.statuses.manage')
                ->name('statuses.index');
            Route::post('/statuses/manage', [AdminProjectRequestStatusController::class, 'store'])
                ->middleware('permission:requests.statuses.manage')
                ->name('statuses.store');
            Route::put('/statuses/manage/{status}', [AdminProjectRequestStatusController::class, 'update'])
                ->middleware('permission:requests.statuses.manage')
                ->name('statuses.update');

            Route::get('/', [AdminProjectRequestController::class, 'index'])
                ->middleware('permission:requests.viewAny')
                ->name('index');
            Route::get('/attachments/{attachment:uuid}', [AccountProjectRequestAttachmentController::class, 'showForStaff'])
                ->middleware('permission:requests.files.download')
                ->name('attachments.show');
            Route::get('/{projectRequest:uuid}', [AdminProjectRequestController::class, 'show'])
                ->middleware('permission:requests.viewAny')
                ->name('show');
            Route::post('/{projectRequest:uuid}/status', [AdminProjectRequestController::class, 'transitionStatus'])
                ->middleware('permission:requests.updateStatus')
                ->name('status.transition');
            Route::post('/{projectRequest:uuid}/comments/internal', [AdminProjectRequestCommentController::class, 'storeInternal'])
                ->middleware('permission:requests.commentInternal')
                ->name('comments.internal.store');
            Route::post('/{projectRequest:uuid}/comments/requester-visible', [AdminProjectRequestCommentController::class, 'storeRequesterVisible'])
                ->middleware('permission:requests.commentPublic')
                ->name('comments.requester-visible.store');
            Route::put('/comments/{comment}/visibility', [AdminProjectRequestCommentController::class, 'updateVisibility'])
                ->middleware('permission:requests.commentPublic')
                ->name('comments.visibility.update');
        });
    });

require __DIR__.'/auth.php';

Route::get('/products', [SiteProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug_current}', [SiteProductController::class, 'show'])->name('products.show');
Route::redirect('/news', '/blog', 301)->name('news.index');
Route::get('/blog', [SiteBlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [SiteBlogController::class, 'show'])->name('blog.show');
Route::get('/faq', [SiteFaqController::class, 'index'])->name('faq.index');
Route::get('/careers', [SiteCareerController::class, 'index'])->name('careers.index');
Route::get('/careers/{job:slug}', [SiteCareerController::class, 'show'])->name('careers.show');
Route::post('/careers/{job:slug}/apply', [SiteCareerController::class, 'apply'])->name('careers.apply');

Route::get('/preview/pages/{version}', CmsPreviewController::class)
    ->name('cms.preview.show');
Route::get('/preview/products/{version}', ProductPreviewController::class)
    ->name('products.preview.show');

Route::get('/{path?}', CmsPageController::class)
    ->where('path', '.*')
    ->name('cms.page.show');
