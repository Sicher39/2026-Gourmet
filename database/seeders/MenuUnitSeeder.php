<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MenuUnit;
use Illuminate\Database\Seeder;

class MenuUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['symbol' => 'g', 'name' => 'Gram', 'type' => 'weight', 'sort_order' => 10],
            ['symbol' => 'kg', 'name' => 'Kilogram', 'type' => 'weight', 'sort_order' => 20],
            ['symbol' => 'ml', 'name' => 'Mililitr', 'type' => 'volume', 'sort_order' => 30],
            ['symbol' => 'l', 'name' => 'Litr', 'type' => 'volume', 'sort_order' => 40],
            ['symbol' => 'ks', 'name' => 'Kus', 'type' => 'count', 'sort_order' => 50],
            ['symbol' => 'porce', 'name' => 'Porce', 'type' => 'count', 'sort_order' => 60],
        ];

        foreach ($units as $unit) {
            MenuUnit::query()->updateOrCreate(
                ['symbol' => $unit['symbol']],
                [
                    'name' => $unit['name'],
                    'type' => $unit['type'],
                    'is_active' => true,
                    'sort_order' => $unit['sort_order'],
                ],
            );
        }
    }
}
