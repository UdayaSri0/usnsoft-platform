<?php

namespace App\Providers;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\AuditSecurity\Models\AuditLog;
use App\Modules\AuditSecurity\Models\FailedLoginAttempt;
use App\Modules\AuditSecurity\Models\SecurityEvent;
use App\Modules\AuditSecurity\Models\UserDevice;
use App\Modules\AuditSecurity\Models\UserLoginHistory;
use App\Modules\AuditSecurity\Models\UserSessionHistory;
use App\Modules\AuditSecurity\Policies\AuditLogPolicy;
use App\Modules\AuditSecurity\Policies\SecurityEventPolicy;
use App\Modules\AuditSecurity\Policies\UserDevicePolicy;
use App\Modules\AuditSecurity\Policies\UserSessionHistoryPolicy;
use App\Modules\IdentityAccess\Models\AccountDeletionRequest;
use App\Modules\IdentityAccess\Models\MfaMethod;
use App\Modules\IdentityAccess\Models\Permission;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Models\SocialAccount;
use App\Modules\IdentityAccess\Models\UserOAuthAccount;
use App\Modules\IdentityAccess\Policies\AccountDeletionRequestPolicy;
use App\Modules\IdentityAccess\Policies\PermissionPolicy;
use App\Modules\IdentityAccess\Policies\RolePolicy;
use App\Modules\IdentityAccess\Policies\UserPolicy;
use App\Modules\Media\Models\MediaAsset;
use App\Modules\Media\Models\MediaAttachment;
use App\Modules\Pages\Models\BlockDefinition;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\PageVersion;
use App\Modules\Pages\Models\PageVersionBlock;
use App\Modules\Pages\Models\PreviewAccessToken;
use App\Modules\Pages\Models\ReusableBlock;
use App\Modules\Pages\Policies\BlockDefinitionPolicy;
use App\Modules\Pages\Policies\PagePolicy;
use App\Modules\Pages\Policies\PageVersionPolicy;
use App\Modules\Pages\Policies\ReusableBlockPolicy;
use App\Modules\Blog\Models\BlogCategory;
use App\Modules\Blog\Models\BlogPost;
use App\Modules\Blog\Models\BlogTag;
use App\Modules\Blog\Policies\BlogPostPolicy;
use App\Modules\Faq\Models\Faq;
use App\Modules\Faq\Models\FaqCategory;
use App\Modules\Faq\Policies\FaqPolicy;
use App\Modules\Careers\Models\Job;
use App\Modules\Careers\Models\JobApplication;
use App\Modules\Careers\Models\JobApplicationFile;
use App\Modules\Careers\Models\JobApplicationNote;
use App\Modules\Careers\Policies\JobApplicationFilePolicy;
use App\Modules\Careers\Policies\JobApplicationPolicy;
use App\Modules\Careers\Policies\JobPolicy;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestAttachment;
use App\Modules\ClientRequests\Models\ProjectRequestComment;
use App\Modules\ClientRequests\Models\ProjectRequestEvent;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use App\Modules\ClientRequests\Policies\ProjectRequestAttachmentPolicy;
use App\Modules\ClientRequests\Policies\ProjectRequestCommentPolicy;
use App\Modules\ClientRequests\Policies\ProjectRequestPolicy;
use App\Modules\Products\Models\ProductCategory;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductDownload;
use App\Modules\Products\Models\ProductDownloadAccess;
use App\Modules\Products\Models\ProductPreviewAccessToken;
use App\Modules\Products\Models\ProductReview;
use App\Modules\Products\Models\ProductTag;
use App\Modules\Products\Models\ProductUserVerification;
use App\Modules\Products\Models\ProductVersion;
use App\Modules\Products\Policies\ProtectedDownloadPolicy;
use App\Modules\Products\Policies\ProductPolicy;
use App\Modules\Products\Policies\ProductReviewPolicy;
use App\Modules\Products\Policies\ProductVersionPolicy;
use App\Modules\Seo\Models\SeoMeta;
use App\Modules\Showcase\Models\Achievement;
use App\Modules\Showcase\Models\Partner;
use App\Modules\Showcase\Models\TeamMember;
use App\Modules\Showcase\Models\Testimonial;
use App\Modules\Showcase\Models\TimelineEntry;
use App\Modules\Showcase\Policies\AchievementPolicy;
use App\Modules\Showcase\Policies\PartnerPolicy;
use App\Modules\Showcase\Policies\TeamMemberPolicy;
use App\Modules\Showcase\Policies\TestimonialPolicy;
use App\Modules\Showcase\Policies\TimelineEntryPolicy;
use App\Modules\SiteSettings\Models\SiteSetting;
use App\Modules\Workflow\Models\ApprovalRecord;
use App\Modules\Workflow\Models\ApprovalRequest;
use App\Modules\Workflow\Models\StatusHistory;
use App\Modules\Workflow\Policies\ApprovalRecordPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(static function (?User $user): ?bool {
            if (! $user) {
                return null;
            }

            return $user->hasRole(CoreRole::SuperAdmin) ? true : null;
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(AccountDeletionRequest::class, AccountDeletionRequestPolicy::class);
        Gate::policy(UserSessionHistory::class, UserSessionHistoryPolicy::class);
        Gate::policy(UserDevice::class, UserDevicePolicy::class);
        Gate::policy(SecurityEvent::class, SecurityEventPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(PageVersion::class, PageVersionPolicy::class);
        Gate::policy(ReusableBlock::class, ReusableBlockPolicy::class);
        Gate::policy(BlockDefinition::class, BlockDefinitionPolicy::class);
        Gate::policy(ApprovalRecord::class, ApprovalRecordPolicy::class);
        Gate::policy(ProjectRequest::class, ProjectRequestPolicy::class);
        Gate::policy(ProjectRequestAttachment::class, ProjectRequestAttachmentPolicy::class);
        Gate::policy(ProjectRequestComment::class, ProjectRequestCommentPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductVersion::class, ProductVersionPolicy::class);
        Gate::policy(ProductReview::class, ProductReviewPolicy::class);
        Gate::policy(BlogPost::class, BlogPostPolicy::class);
        Gate::policy(Faq::class, FaqPolicy::class);
        Gate::policy(Job::class, JobPolicy::class);
        Gate::policy(JobApplication::class, JobApplicationPolicy::class);
        Gate::policy(JobApplicationFile::class, JobApplicationFilePolicy::class);
        Gate::policy(Testimonial::class, TestimonialPolicy::class);
        Gate::policy(Partner::class, PartnerPolicy::class);
        Gate::policy(TeamMember::class, TeamMemberPolicy::class);
        Gate::policy(TimelineEntry::class, TimelineEntryPolicy::class);
        Gate::policy(Achievement::class, AchievementPolicy::class);

        Gate::define('admin.access', static function (User $user): bool {
            return $user->isInternalStaff() && $user->hasPermission('admin.access');
        });

        Gate::define('superadmin.access', static function (User $user): bool {
            return $user->hasRole(CoreRole::SuperAdmin);
        });

        Gate::define('downloads.protected.access', [ProtectedDownloadPolicy::class, 'access']);

        Gate::define('requests.create', static function (User $user): bool {
            return $user->hasVerifiedEmail()
                && $user->isActiveForAuthentication()
                && $user->hasPermission('requests.create');
        });

        Relation::enforceMorphMap([
            'user' => User::class,
            'role' => Role::class,
            'permission' => Permission::class,
            'site_setting' => SiteSetting::class,
            'media_asset' => MediaAsset::class,
            'media_attachment' => MediaAttachment::class,
            'approval_request' => ApprovalRequest::class,
            'status_history' => StatusHistory::class,
            'audit_log' => AuditLog::class,
            'security_event' => SecurityEvent::class,
            'social_account' => SocialAccount::class,
            'user_oauth_account' => UserOAuthAccount::class,
            'mfa_method' => MfaMethod::class,
            'user_login_history' => UserLoginHistory::class,
            'user_session_history' => UserSessionHistory::class,
            'user_device' => UserDevice::class,
            'failed_login_attempt' => FailedLoginAttempt::class,
            'account_deletion_request' => AccountDeletionRequest::class,
            'page' => Page::class,
            'page_version' => PageVersion::class,
            'page_version_block' => PageVersionBlock::class,
            'block_definition' => BlockDefinition::class,
            'reusable_block' => ReusableBlock::class,
            'preview_access_token' => PreviewAccessToken::class,
            'seo_meta' => SeoMeta::class,
            'approval_record' => ApprovalRecord::class,
            'project_request' => ProjectRequest::class,
            'project_request_status' => ProjectRequestStatus::class,
            'project_request_comment' => ProjectRequestComment::class,
            'project_request_attachment' => ProjectRequestAttachment::class,
            'project_request_event' => ProjectRequestEvent::class,
            'product_category' => ProductCategory::class,
            'product' => Product::class,
            'product_version' => ProductVersion::class,
            'product_download' => ProductDownload::class,
            'product_download_access' => ProductDownloadAccess::class,
            'product_review' => ProductReview::class,
            'product_tag' => ProductTag::class,
            'product_user_verification' => ProductUserVerification::class,
            'product_preview_access_token' => ProductPreviewAccessToken::class,
            'blog_category' => BlogCategory::class,
            'blog_tag' => BlogTag::class,
            'blog_post' => BlogPost::class,
            'faq_category' => FaqCategory::class,
            'faq' => Faq::class,
            'job' => Job::class,
            'job_application' => JobApplication::class,
            'job_application_file' => JobApplicationFile::class,
            'job_application_note' => JobApplicationNote::class,
            'testimonial' => Testimonial::class,
            'partner' => Partner::class,
            'team_member' => TeamMember::class,
            'timeline_entry' => TimelineEntry::class,
            'achievement' => Achievement::class,
        ]);
    }
}
