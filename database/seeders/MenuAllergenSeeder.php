<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MenuAllergen;
use Illuminate\Database\Seeder;

class MenuAllergenSeeder extends Seeder
{
    public function run(): void
    {
        $allergens = [
            ['code' => '1', 'name' => 'Obiloviny obsahující lepek', 'description' => 'Pšenice, žito, ječmen, oves, špalda, kamut nebo jejich hybridní odrůdy a výrobky z nich.', 'sort_order' => 10],
            ['code' => '2', 'name' => 'Korýši', 'description' => 'Korýši a výrobky z nich.', 'sort_order' => 20],
            ['code' => '3', 'name' => 'Vejce', 'description' => 'Vejce a výrobky z nich.', 'sort_order' => 30],
            ['code' => '4', 'name' => 'Ryby', 'description' => 'Ryby a výrobky z nich.', 'sort_order' => 40],
            ['code' => '5', 'name' => 'Podzemnice olejná', 'description' => 'Arašídy a výrobky z nich.', 'sort_order' => 50],
            ['code' => '6', 'name' => 'Sójové boby', 'description' => 'Sójové boby a výrobky z nich.', 'sort_order' => 60],
            ['code' => '7', 'name' => 'Mléko', 'description' => 'Mléko a výrobky z něj včetně laktózy.', 'sort_order' => 70],
            ['code' => '8', 'name' => 'Skořápkové plody', 'description' => 'Mandle, lískové ořechy, vlašské ořechy, kešu, pekanové ořechy, para ořechy, pistácie, makadamie a výrobky z nich.', 'sort_order' => 80],
            ['code' => '9', 'name' => 'Celer', 'description' => 'Celer a výrobky z něj.', 'sort_order' => 90],
            ['code' => '10', 'name' => 'Hořčice', 'description' => 'Hořčice a výrobky z ní.', 'sort_order' => 100],
            ['code' => '11', 'name' => 'Sezamová semena', 'description' => 'Sezamová semena a výrobky z nich.', 'sort_order' => 110],
            ['code' => '12', 'name' => 'Oxid siřičitý a siřičitany', 'description' => 'Oxid siřičitý a siřičitany v koncentracích vyšších než 10 mg/kg nebo 10 mg/l.', 'sort_order' => 120],
            ['code' => '13', 'name' => 'Vlčí bob', 'description' => 'Vlčí bob a výrobky z něj.', 'sort_order' => 130],
            ['code' => '14', 'name' => 'Měkkýši', 'description' => 'Měkkýši a výrobky z nich.', 'sort_order' => 140],
        ];

        foreach ($allergens as $allergen) {
            MenuAllergen::query()->updateOrCreate(
                ['code' => $allergen['code']],
                [
                    'name' => $allergen['name'],
                    'description' => $allergen['description'],
                    'is_active' => true,
                    'sort_order' => $allergen['sort_order'],
                ],
            );
        }
    }
}
