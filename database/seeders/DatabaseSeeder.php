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
            CompanyProfileSeeder::class,
            RestaurantContactInformationSeeder::class,
            MenuCatalogTypeSeeder::class,
            MenuAllergenSeeder::class,
            MenuUnitSeeder::class,
            MenuCatalogItemSeeder::class,
            BreakfastMenuSeeder::class,
            SeoPagesSeeder::class,
            LegalDocumentSeeder::class,
            DataProcessingPurposeSeeder::class,
            TrackingScriptSeeder::class,
            CookieSettingSeeder::class,
        ]);
    }
}
