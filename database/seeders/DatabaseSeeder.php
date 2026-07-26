<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MenuAllergenSeeder::class,
            MenuUnitSeeder::class,
            SeoPagesSeeder::class,
            TrackingScriptSeeder::class,
            CookieSettingSeeder::class,
            DataProcessingPurposeSeeder::class,
            LegalDocumentSeeder::class,
        ]);
    }
}
