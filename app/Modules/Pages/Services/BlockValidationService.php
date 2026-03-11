<?php

namespace App\Modules\Pages\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BlockValidationService
{
    public function __construct(
        private readonly BlockRegistryService $registry,
        private readonly BlockSanitizerService $sanitizer,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validateAndNormalize(string $blockType, array $data): array
    {
        $rules = $this->registry->rules($blockType);

        if ($rules === []) {
            throw ValidationException::withMessages([
                'block_type' => "Unsupported block type [{$blockType}].",
            ]);
        }

        $payload = array_replace_recursive($this->registry->defaultData($blockType), $data);
        $payload = $this->sanitizer->sanitize($blockType, $payload);

        $validated = Validator::make($payload, $rules)->validate();

        return $this->removeUnknownRootKeys($validated, $rules);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function removeUnknownRootKeys(array $validated, array $rules): array
    {
        $allowedRootKeys = collect(array_keys($rules))
            ->filter(static fn (string $key): bool => ! str_contains($key, '.'))
            ->values()
            ->all();

        if ($allowedRootKeys === []) {
            return $validated;
        }

        $result = [];

        foreach ($allowedRootKeys as $rootKey) {
            if (! Arr::exists($validated, $rootKey)) {
                continue;
            }

            $result[$rootKey] = $validated[$rootKey];
        }

        return $result;
    }
}
