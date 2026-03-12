<?php

namespace App\Modules\Careers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobApplicationNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'note_body' => ['required', 'string', 'max:5000'],
        ];
    }
}
