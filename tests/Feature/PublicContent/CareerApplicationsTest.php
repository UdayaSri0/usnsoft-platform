<?php

namespace Tests\Feature\PublicContent;

use App\Enums\CoreRole;
use App\Modules\Careers\Enums\JobApplicationStatus;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Feature\PublicContent\Concerns\InteractsWithPublicContent;
use Tests\TestCase;

class CareerApplicationsTest extends TestCase
{
    use InteractsWithPublicContent;
    use RefreshDatabase;

    public function test_public_applications_validate_file_types_and_store_files_privately(): void
    {
        $this->seedPublicContentCore();
        Storage::fake('local');

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $job = $this->createJob($superAdmin, [
            'title' => 'Backend Engineer',
            'slug' => 'backend-engineer',
        ]);

        $this->post(route('careers.apply', ['job' => $job->slug]), [
            'full_name' => 'Jane Applicant',
            'email' => 'jane@example.test',
            'cv' => UploadedFile::fake()->image('resume.png'),
        ])->assertSessionHasErrors('cv');

        $this->post(route('careers.apply', ['job' => $job->slug]), [
            'full_name' => 'Jane Applicant',
            'email' => 'jane@example.test',
            'phone' => '555-0110',
            'cover_message' => 'Protected applicant submission.',
            'cv' => UploadedFile::fake()->create('resume.pdf', 64, 'application/pdf'),
            'cover_letter' => UploadedFile::fake()->create('cover-letter.docx', 64, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ])->assertRedirect(route('careers.show', ['job' => $job->slug]));

        $application = $job->applications()->with('files.mediaAsset')->firstOrFail();
        $file = $application->files->firstOrFail();

        $this->assertSame('local', $file->disk);
        $this->assertSame('protected', $file->mediaAsset?->visibility?->value);
        Storage::disk('local')->assertExists($file->path);
    }

    public function test_applicant_records_and_files_are_protected_but_authorized_staff_can_review_and_audit_actions(): void
    {
        $this->seedPublicContentCore();
        Storage::fake('local');

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $admin = $this->makeUserWithRole(CoreRole::Admin);
        $publicUser = $this->makeUserWithRole(CoreRole::User);
        $job = $this->createJob($superAdmin, [
            'title' => 'Security Analyst',
            'slug' => 'security-analyst',
        ]);

        $application = $this->submitApplication($job);
        $file = $application->files()->firstOrFail();

        $this->actingAs($publicUser)
            ->get(route('admin.careers.applications.show', ['application' => $application->getKey()]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.careers.applications.show', ['application' => $application->getKey()]))
            ->assertOk()
            ->assertSee('Jane Applicant');

        $this->actingAs($admin)
            ->get(route('admin.careers.applications.files.download', ['file' => $file->getKey()]))
            ->assertOk();

        $this->actingAs($admin)
            ->put(route('admin.careers.applications.status.update', ['application' => $application->getKey()]), [
                'status' => JobApplicationStatus::UnderReview->value,
                'note' => 'Initial screening complete.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $application->getMorphClass(),
            'auditable_id' => $application->getKey(),
            'event_type' => 'careers.application.status_changed',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $file->getMorphClass(),
            'auditable_id' => $file->getKey(),
            'event_type' => 'careers.application.file_downloaded',
        ]);
    }

    public function test_expired_jobs_reject_new_applications(): void
    {
        $this->seedPublicContentCore();
        Storage::fake('local');

        $superAdmin = $this->makeUserWithRole(CoreRole::SuperAdmin);
        $job = $this->createJob($superAdmin, [
            'title' => 'Expired Role',
            'slug' => 'expired-role',
            'deadline' => CarbonImmutable::now()->subDay(),
        ]);

        $this->expectException(ValidationException::class);

        $this->submitApplication($job);
    }
}
