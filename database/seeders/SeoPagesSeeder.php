<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SeoPage;
use Illuminate\Database\Seeder;

class SeoPagesSeeder extends Seeder
{
    /**
     * Legacy foreign-project keys that must be removed when present.
     * Arbitrary admin/custom records are preserved untouched.
     */
    private const LEGACY_KEYS_TO_REMOVE = [
        'front-therapy',
        'front-marriage-counseling',
        'front-individual-consultations',
        'front-coaching',
        'front-media',
    ];

    public function run(): void
    {
        // Remove only explicit legacy foreign-project keys.
        SeoPage::query()
            ->whereIn('key', self::LEGACY_KEYS_TO_REMOVE)
            ->delete();

        // Upsert the canonical restaurant defaults.
        foreach (SeoPage::defaultRecords() as $record) {
            SeoPage::query()->updateOrCreate(
                ['key' => $record['key']],
                $record,
            );
        }
    }
}
