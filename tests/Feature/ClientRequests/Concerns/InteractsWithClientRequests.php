<?php

namespace Tests\Feature\ClientRequests\Concerns;

use App\Enums\AccountStatus;
use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestSystemStatus;
use App\Modules\ClientRequests\Enums\ProjectRequestType;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use App\Modules\ClientRequests\Services\ProjectRequestStatusService;
use App\Modules\ClientRequests\Services\ProjectRequestSubmissionService;
use App\Modules\IdentityAccess\Models\Role;
use Database\Seeders\CoreRoleSeeder;
use Database\Seeders\PermissionScaffoldSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait InteractsWithClientRequests
{
    protected function seedClientRequestCore(): void
    {
        $this->seed([
            CoreRoleSeeder::class,
            PermissionScaffoldSeeder::class,
            RolePermissionSeeder::class,
        ]);

        Storage::fake((string) config('client_requests.upload_disk', 'local'));
    }

    protected function makeUserWithRole(CoreRole $role, bool $verified = true): User
    {
        $user = User::factory()->create([
            'email_verified_at' => $verified ? now() : null,
            'status' => AccountStatus::Active->value,
            'is_internal' => in_array($role, CoreRole::internalRoles(), true),
        ]);

        $roleModel = Role::query()->where('name', $role->value)->firstOrFail();
        $user->assignRole($roleModel, $user->getKey());

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function projectRequestPayload(?User $user = null, array $overrides = []): array
    {
        $payload = [
            'requester_name' => $user?->name ?? 'Client Requester',
            'company_name' => 'USNsoft Labs',
            'contact_email' => $user?->email ?? 'client@example.test',
            'contact_phone' => $user?->phone ?? '+1 555 100 200',
            'project_title' => 'Protected request intake build',
            'project_summary' => 'Need an internal request tracking workflow with protected documents.',
            'project_description' => 'We need a verified-user project request flow with private attachments, visibility-aware comments, and audited workflow history for staff operations.',
            'budget' => 12500,
            'deadline' => now()->addDays(21)->toDateString(),
            'project_type' => ProjectRequestType::ProjectIdea->value,
            'requested_features' => "Secure uploads\nStatus timeline\nRequester-visible updates",
            'preferred_tech_stack' => "Laravel\nPostgreSQL\nRedis",
            'preferred_meeting_availability' => "Weekdays after 14:00 UTC\nThursday morning",
        ];

        return array_merge($payload, $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @param  array<int, UploadedFile>  $attachments
     */
    protected function submitProjectRequest(User $user, array $overrides = [], array $attachments = []): ProjectRequest
    {
        return app(ProjectRequestSubmissionService::class)->submit(
            $user,
            $this->projectRequestPayload($user, $overrides),
            $attachments,
        );
    }

    protected function uploadedPdf(string $name = 'scope.pdf', int $sizeKb = 128): UploadedFile
    {
        return UploadedFile::fake()->create($name, $sizeKb, 'application/pdf');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createCustomStatus(User $actor, array $overrides = []): ProjectRequestStatus
    {
        return app(ProjectRequestStatusService::class)->createCustomStatus($actor, array_merge([
            'name' => 'Awaiting Vendor Response',
            'code' => 'awaiting_vendor_response',
            'system_status' => ProjectRequestSystemStatus::UnderReview->value,
            'sort_order' => 250,
            'badge_tone' => 'warning',
            'visible_to_requester' => false,
            'is_terminal' => false,
        ], $overrides));
    }

    protected function requestStatus(string $code): ProjectRequestStatus
    {
        return ProjectRequestStatus::query()->where('code', $code)->firstOrFail();
    }
}
