<?php

namespace App\Modules\Careers\Requests;

use App\Enums\VisibilityState;
use App\Modules\Careers\Models\Job;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $job = $this->route('job');

        if ($job instanceof Job) {
            return $this->user()?->can('update', $job) ?? false;
        }

        return $this->user()?->can('create', Job::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $job = $this->route('job');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:190',
                'regex:/^[a-z0-9\\-\\/]+$/',
                Rule::unique('jobs', 'slug')->ignore($job instanceof Job ? $job->getKey() : null),
            ],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:20000'],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:80'],
            'department' => ['nullable', 'string', 'max:120'],
            'level' => ['nullable', 'string', 'max:120'],
            'deadline' => ['nullable', 'date'],
            'featured_flag' => ['nullable', 'boolean'],
            'visibility' => ['required', Rule::in(VisibilityState::values())],
            'change_notes' => ['nullable', 'string', 'max:2000'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:500'],
            'seo.og_image_media_id' => ['nullable', 'exists:media_assets,id'],
            'seo.robots_index' => ['nullable', 'boolean'],
            'seo.robots_follow' => ['nullable', 'boolean'],
            'seo.schema_type' => ['nullable', 'string', 'max:80'],
        ];
    }
}
