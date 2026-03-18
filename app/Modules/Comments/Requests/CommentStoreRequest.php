<?php

namespace App\Modules\Comments\Requests;

use App\Http\Requests\Concerns\ValidatesAntiSpam;
use Illuminate\Foundation\Http\FormRequest;

class CommentStoreRequest extends FormRequest
{
    use ValidatesAntiSpam;

    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->isActiveForAuthentication()
            && $user->hasVerifiedEmail()
            && $user->hasPermission('comments.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    protected function antiSpamFormKey(): string
    {
        return 'blog_comment';
    }
}
