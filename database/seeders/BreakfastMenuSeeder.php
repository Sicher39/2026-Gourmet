<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BreakfastCatalogItem;
use App\Models\BreakfastMenu;
use App\Models\MenuAllergen;
use App\Models\RestaurantContactInformation;
use Illuminate\Database\Seeder;

class BreakfastMenuSeeder extends Seeder
{
    public function run(): void
    {
        $catalogItems = collect($this->catalogItems())
            ->mapWithKeys(function (array $item): array {
                $catalogItem = BreakfastCatalogItem::query()->updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'default_price' => $item['price'],
                        'is_active' => true,
                        'sort_order' => $item['sort_order'],
                    ],
                );

                $catalogItem->allergens()->sync(
                    MenuAllergen::query()
                        ->whereIn('code', $item['allergens'])
                        ->pluck('id')
                        ->all(),
                );

                return [$item['name'] => $catalogItem];
            });

        foreach ($this->menus() as $menuData) {
            $restaurant = RestaurantContactInformation::query()
                ->where('business_name', $menuData['branch'])
                ->firstOrFail();

            $menu = BreakfastMenu::query()->updateOrCreate(
                [
                    'restaurant_contact_information_id' => $restaurant->getKey(),
                    'valid_from' => '2026-01-01',
                ],
                [
                    'valid_to' => null,
                    'is_active' => true,
                ],
            );

            $menu->items()->delete();

            foreach ($menuData['items'] as $sortOrder => $itemData) {
                /** @var BreakfastCatalogItem $catalogItem */
                $catalogItem = $catalogItems->get($itemData['name']);
                $allergens = $this->allergensFor($itemData['name']);

                $menuItem = $menu->items()->create([
                    'breakfast_catalog_item_id' => $catalogItem->getKey(),
                    'name_snapshot' => $catalogItem->name,
                    'allergens_snapshot' => $allergens,
                    'price' => $catalogItem->default_price,
                    'is_available' => true,
                    'sort_order' => ($sortOrder + 1) * 10,
                ]);

                foreach ($itemData['variants'] as $variantSortOrder => $variant) {
                    $menuItem->variants()->create([
                        'name' => $variant['name'],
                        'allergens_snapshot' => $variant['allergens'],
                        'sort_order' => ($variantSortOrder + 1) * 10,
                    ]);
                }
            }
        }
    }

    /**
     * @return array<int, array{name: string, price: int, allergens: array<int, string>, sort_order: int}>
     */
    private function catalogItems(): array
    {
        return [
            ['name' => 'Vejce Benedikt', 'price' => 155, 'allergens' => ['1', '3', '7'], 'sort_order' => 10],
            ['name' => 'Anglická snídaně – fazole, klobása, opečená slanina, sázené vejce, zelenina', 'price' => 135, 'allergens' => ['3'], 'sort_order' => 20],
            ['name' => 'Hemenex – vejce 3ks, šunka, zelenina', 'price' => 99, 'allergens' => ['3'], 'sort_order' => 30],
            ['name' => 'Míchaná vajíčka – vejce 3ks, anglická slanina, zelenina', 'price' => 95, 'allergens' => ['3'], 'sort_order' => 40],
            ['name' => 'Vaječná omeleta – vejce 3ks, eidam, zelenina', 'price' => 119, 'allergens' => ['3', '7'], 'sort_order' => 50],
            ['name' => 'Debrecínské párky s hořčicí a kečupem', 'price' => 95, 'allergens' => ['10'], 'sort_order' => 60],
            ['name' => 'Plněná bageta', 'price' => 75, 'allergens' => ['1'], 'sort_order' => 70],
            ['name' => 'Plněné tousty', 'price' => 99, 'allergens' => ['1'], 'sort_order' => 80],
            ['name' => 'Ovesná kaše s ovocným rozvarem malá', 'price' => 75, 'allergens' => ['1'], 'sort_order' => 90],
            ['name' => 'Ovesná kaše s ovocným rozvarem velká', 'price' => 75, 'allergens' => ['1'], 'sort_order' => 100],
            ['name' => 'Jogurt s müsli a čerstvým ovocem', 'price' => 75, 'allergens' => ['7'], 'sort_order' => 110],
            ['name' => 'Jogurt s medem a vlašskými ořechy', 'price' => 75, 'allergens' => ['7', '8'], 'sort_order' => 120],
        ];
    }

    /**
     * @return array<int, array{branch: string, items: array<int, array{name: string, variants: array<int, array{name: string, allergens: array<int, string>}>}>}>
     */
    private function menus(): array
    {
        return [
            [
                'branch' => 'Gourmet U Vaňkovky',
                'items' => [
                    ['name' => 'Vejce Benedikt', 'variants' => []],
                    ['name' => 'Anglická snídaně – fazole, klobása, opečená slanina, sázené vejce, zelenina', 'variants' => []],
                    ['name' => 'Hemenex – vejce 3ks, šunka, zelenina', 'variants' => []],
                    ['name' => 'Míchaná vajíčka – vejce 3ks, anglická slanina, zelenina', 'variants' => []],
                    ['name' => 'Vaječná omeleta – vejce 3ks, eidam, zelenina', 'variants' => []],
                    ['name' => 'Debrecínské párky s hořčicí a kečupem', 'variants' => []],
                ],
            ],
            [
                'branch' => 'Gourmet Ponávka',
                'items' => [
                    ['name' => 'Míchaná vajíčka – vejce 3ks, anglická slanina, zelenina', 'variants' => []],
                    ['name' => 'Hemenex – vejce 3ks, šunka, zelenina', 'variants' => []],
                    ['name' => 'Vaječná omeleta – vejce 3ks, eidam, zelenina', 'variants' => []],
                    ['name' => 'Anglická snídaně – fazole, klobása, opečená slanina, sázené vejce, zelenina', 'variants' => []],
                    ['name' => 'Debrecínské párky s hořčicí a kečupem', 'variants' => []],
                    [
                        'name' => 'Plněná bageta',
                        'variants' => [
                            ['name' => 'Italiana (máslo, prosciutto, mozzarella, bazalka, rajče)', 'allergens' => ['7']],
                            ['name' => 'Sýrová (máslo, mozzarella, hermelín, eidam, zelenina)', 'allergens' => ['7']],
                            ['name' => 'Šunková (máslo, dušená šunka, zelenina)', 'allergens' => ['7']],
                            ['name' => 'Kuřecí (kuřecí maso, zelenina, jogurtový dressing)', 'allergens' => ['7']],
                            ['name' => 'Anglická (anglická slanina, niva, zelenina, dressing)', 'allergens' => ['7']],
                        ],
                    ],
                    [
                        'name' => 'Plněné tousty',
                        'variants' => [
                            ['name' => 'Tuňákový (majonéza, tuňák, vařené vejce, listový salát)', 'allergens' => []],
                            ['name' => 'S uzeným masem (máslo, hořčice, uzené maso, sterilovaný okurek, listový salát)', 'allergens' => []],
                        ],
                    ],
                    ['name' => 'Ovesná kaše s ovocným rozvarem malá', 'variants' => []],
                    ['name' => 'Ovesná kaše s ovocným rozvarem velká', 'variants' => []],
                    ['name' => 'Jogurt s müsli a čerstvým ovocem', 'variants' => []],
                    ['name' => 'Jogurt s medem a vlašskými ořechy', 'variants' => []],
                ],
            ],
        ];
    }

    /** @return array<int, string> */
    private function allergensFor(string $catalogItemName): array
    {
        return collect($this->catalogItems())
            ->firstWhere('name', $catalogItemName)['allergens'];
    }
}
