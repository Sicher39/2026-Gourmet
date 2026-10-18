<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SeoPage;
use Illuminate\Database\Seeder;

class SeoPagesSeeder extends Seeder
{
    public function run(): void
    {
        $records = SeoPage::defaultRecords();
        $keys = collect($records)->pluck('key')->all();

        foreach ($records as $record) {
            SeoPage::query()->updateOrCreate(
                ['key' => $record['key']],
                $record,
            );
        }

        SeoPage::query()->whereNotIn('key', $keys)->delete();
        SeoPage::query()->where('is_global', true)->update(['is_global' => false]);
        SeoPage::query()->where('key', 'gourmet-main')->update(['is_global' => true]);
    }
}
