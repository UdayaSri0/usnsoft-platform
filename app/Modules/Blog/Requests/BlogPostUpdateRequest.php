<?php

namespace App\Modules\Blog\Requests;

use App\Modules\Blog\Models\BlogPost;
use Illuminate\Validation\Rule;

class BlogPostUpdateRequest extends BlogPostStoreRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $post = $this->route('post');

        $rules['slug'] = [
            'required',
            'string',
            'max:190',
            'regex:/^[a-z0-9\\-\\/]+$/',
            Rule::unique('blog_posts', 'slug')->ignore($post instanceof BlogPost ? $post->getKey() : null),
        ];

        return $rules;
    }
}
