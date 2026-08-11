<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MenuCatalogType;
use Illuminate\Database\Seeder;

class MenuCatalogTypeSeeder extends Seeder
{
    public function run(): void
    {
        $catalogTypes = [
            ['name' => 'Polévky', 'slug' => 'polevky', 'is_active' => true, 'sort_order' => 100],
            ['name' => 'Hlavní jídla', 'slug' => 'hlavni-jidla', 'is_active' => true, 'sort_order' => 200],
            ['name' => 'Přílohy', 'slug' => 'prilohy', 'is_active' => true, 'sort_order' => 300],
            ['name' => 'omáčky a ostatní', 'slug' => 'omacky-a-ostatni', 'is_active' => true, 'sort_order' => 400],
            ['name' => 'pizza', 'slug' => 'pizza', 'is_active' => true, 'sort_order' => 500],
            ['name' => 'grill', 'slug' => 'grill', 'is_active' => true, 'sort_order' => 600],
        ];

        foreach ($catalogTypes as $catalogType) {
            MenuCatalogType::withTrashed()->updateOrCreate(
                ['slug' => $catalogType['slug']],
                [...$catalogType, 'deleted_at' => null],
            );
        }
    }
}
