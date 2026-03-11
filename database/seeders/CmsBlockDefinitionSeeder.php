<?php

namespace Database\Seeders;

use App\Modules\Pages\Services\BlockRegistryService;
use Illuminate\Database\Seeder;

class CmsBlockDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        app(BlockRegistryService::class)->syncToDatabase();
    }
}
