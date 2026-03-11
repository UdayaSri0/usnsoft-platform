<?php

namespace App\Modules\Pages\Services;

use App\Modules\Pages\Models\BlockDefinition;
use Illuminate\Support\Arr;

class BlockRegistryService
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function allFromConfig(): array
    {
        $definitions = config('cms.definitions', []);

        return is_array($definitions) ? $definitions : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function definition(string $key): ?array
    {
        $definition = Arr::get($this->allFromConfig(), $key);

        return is_array($definition) ? $definition : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(string $key): array
    {
        $definition = $this->definition($key);

        if (! $definition) {
            return [];
        }

        $rules = $definition['rules'] ?? [];

        return is_array($rules) ? $rules : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultData(string $key): array
    {
        $definition = $this->definition($key);

        if (! $definition) {
            return [];
        }

        $defaultData = $definition['default_data'] ?? [];

        return is_array($defaultData) ? $defaultData : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultLayout(string $key): array
    {
        $definition = $this->definition($key);

        if (! $definition) {
            return [];
        }

        $defaultLayout = $definition['default_layout'] ?? [];

        return is_array($defaultLayout) ? $defaultLayout : [];
    }

    public function syncToDatabase(): void
    {
        foreach ($this->allFromConfig() as $key => $definition) {
            BlockDefinition::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name' => (string) ($definition['name'] ?? $key),
                    'category' => (string) ($definition['category'] ?? 'general'),
                    'description' => $definition['description'] ?? null,
                    'icon' => $definition['icon'] ?? null,
                    'schema_json' => $definition['schema'] ?? null,
                    'default_data_json' => $definition['default_data'] ?? null,
                    'default_layout_json' => $definition['default_layout'] ?? null,
                    'editor_mode' => (string) ($definition['editor_mode'] ?? 'basic'),
                    'is_reusable_allowed' => (bool) ($definition['is_reusable_allowed'] ?? true),
                    'is_active' => true,
                    'is_system' => true,
                    'sort_order' => (int) ($definition['sort_order'] ?? 0),
                    'rendering_view' => (string) ($definition['rendering_view'] ?? 'components.blocks.fallback'),
                    'rendering_component_class' => $definition['rendering_component_class'] ?? null,
                ],
            );
        }
    }
}
