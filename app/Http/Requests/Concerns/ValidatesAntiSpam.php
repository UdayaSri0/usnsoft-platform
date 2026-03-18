<?php

namespace App\Http\Requests\Concerns;

use App\Services\Security\AntiSpam\AntiSpamService;
use Illuminate\Validation\Validator;

trait ValidatesAntiSpam
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $result = app(AntiSpamService::class)->verifyRequest($this, $this->antiSpamFormKey(), $this->user());

            if (! $result->passed) {
                $validator->errors()->add('anti_spam', $result->message ?? 'Anti-spam verification failed.');
            }
        });
    }

    abstract protected function antiSpamFormKey(): string;
}
