<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SeoPage;
use Illuminate\Database\Seeder;

class SeoPagesSeeder extends Seeder
{
    public function run(): void
    {
        SeoPage::query()->delete();

        foreach (SeoPage::defaultRecords() as $record) {
            SeoPage::query()->create($record);
        }
    }
}
