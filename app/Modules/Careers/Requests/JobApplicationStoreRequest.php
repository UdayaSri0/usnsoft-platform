<?php

namespace App\Modules\Careers\Requests;

use App\Http\Requests\Concerns\ValidatesAntiSpam;
use Illuminate\Foundation\Http\FormRequest;

class JobApplicationStoreRequest extends FormRequest
{
    use ValidatesAntiSpam;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = (int) config('careers.max_upload_kb', 10240);
        $extensions = implode(',', (array) config('careers.allowed_extensions', ['pdf', 'doc', 'docx']));

        return [
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:500'],
            'cover_message' => ['nullable', 'string', 'max:5000'],
            'portfolio_url' => ['nullable', 'url', 'max:2048'],
            'linkedin_url' => ['nullable', 'url', 'max:2048'],
            'github_url' => ['nullable', 'url', 'max:2048'],
            'cv' => ['required', 'file', 'mimes:'.$extensions, 'max:'.$maxKb],
            'cover_letter' => ['nullable', 'file', 'mimes:'.$extensions, 'max:'.$maxKb],
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['file', 'mimes:'.$extensions, 'max:'.$maxKb],
        ];
    }

    protected function antiSpamFormKey(): string
    {
        return 'careers_application';
    }
}
