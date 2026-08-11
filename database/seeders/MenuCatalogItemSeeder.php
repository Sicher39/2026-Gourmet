<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuCatalogItemSeeder extends Seeder
{
    public function run(): void
    {
        $catalogTypeIds = DB::table('menu_catalog_types')
            ->whereIn('slug', ['polevky', 'hlavni-jidla', 'prilohy', 'omacky-a-ostatni', 'pizza', 'grill'])
            ->whereNull('deleted_at')
            ->pluck('id', 'slug');

        $allergenIds = DB::table('menu_allergens')
            ->whereNull('deleted_at')
            ->pluck('id', 'code');

        $gramUnitId = DB::table('menu_units')
            ->where('symbol', 'g')
            ->whereNull('deleted_at')
            ->value('id');

        foreach ($this->items() as $item) {
            $catalogTypeId = $catalogTypeIds[$item['catalog_type']] ?? null;
            $amount = $item['amount'] ?? 130;

            if ($catalogTypeId === null) {
                continue;
            }

            $attributes = [
                'menu_catalog_type_id' => $catalogTypeId,
                'name' => $item['name'],
            ];

            $values = [
                'menu_unit_id' => $gramUnitId,
                'amount' => $amount,
                'is_active' => true,
                'sort_order' => $item['sort_order'],
                'deleted_at' => null,
                'updated_at' => now(),
            ];

            $catalogItem = DB::table('menu_catalog_items')
                ->where($attributes)
                ->first();

            if ($catalogItem === null) {
                $catalogItemId = DB::table('menu_catalog_items')->insertGetId([
                    ...$attributes,
                    ...$values,
                    'created_at' => now(),
                ]);
            } else {
                DB::table('menu_catalog_items')
                    ->where('id', $catalogItem->id)
                    ->update($values);

                $catalogItemId = $catalogItem->id;
            }

            DB::table('menu_allergen_menu_catalog_item')
                ->where('menu_catalog_item_id', $catalogItemId)
                ->delete();

            $allergenRows = collect($item['allergens'])
                ->map(fn (string $code): ?array => isset($allergenIds[$code])
                    ? ['menu_catalog_item_id' => $catalogItemId, 'menu_allergen_id' => $allergenIds[$code]]
                    : null)
                ->filter()
                ->all();

            if ($allergenRows !== []) {
                DB::table('menu_allergen_menu_catalog_item')->insert($allergenRows);
            }
        }
    }

    /**
     * @return array<int, array{catalog_type: string, name: string, amount: int|float|null, allergens: array<int, string>, sort_order: int}>
     */
    private function items(): array
    {
        return [
            ['catalog_type' => 'polevky', 'name' => 'Česnečka s opečeným chlebem', 'amount' => null, 'allergens' => ['1', '3', '9'], 'sort_order' => 10],
            ['catalog_type' => 'polevky', 'name' => 'Luštěninová polévka', 'amount' => null, 'allergens' => ['1', '9'], 'sort_order' => 20],
            ['catalog_type' => 'polevky', 'name' => 'Kuřecí vývar s masem, zeleninou a nudlemi', 'amount' => null, 'allergens' => ['1', '3', '9'], 'sort_order' => 30],
            ['catalog_type' => 'polevky', 'name' => 'Brokolicový krém', 'amount' => null, 'allergens' => ['1', '7', '9'], 'sort_order' => 40],

            ['catalog_type' => 'hlavni-jidla', 'name' => 'Smažený soukenický řízek z mletého masa', 'amount' => 140, 'allergens' => ['1', '3', '7'], 'sort_order' => 10],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Zapečená císařská zelenina smetanou a sýrem', 'amount' => null, 'allergens' => ['1', '3', '7', '9'], 'sort_order' => 20],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Hovězí nudličky bourgignon', 'amount' => 130, 'allergens' => ['1', '3', '7', '9', '10', '12'], 'sort_order' => 30],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Kuřecí čína se zeleninou a klíčky', 'amount' => 130, 'allergens' => ['1', '6', '9'], 'sort_order' => 40],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Lehký zeleninový salát s kuřecími stripsy', 'amount' => 130, 'allergens' => ['1', '3', '7', '10'], 'sort_order' => 50],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Fajitas (kuřecí stehenní maso, cibule, paprika)', 'amount' => 130, 'allergens' => ['1', '3', '7', '8', '9', '10'], 'sort_order' => 60],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Vepřový guláš s cibulí', 'amount' => 130, 'allergens' => ['1', '3', '7'], 'sort_order' => 70],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Smažené kapustové karbanátky', 'amount' => 150, 'allergens' => ['1', '3', '7', '10'], 'sort_order' => 80],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Anglický roastbeef', 'amount' => 130, 'allergens' => ['1', '6', '10'], 'sort_order' => 90],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Vařené hovězí maso', 'amount' => 130, 'allergens' => ['1', '3', '7', '9'], 'sort_order' => 100],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Vařená vejce', 'amount' => null, 'allergens' => ['3'], 'sort_order' => 110],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Lívanečky', 'amount' => 300, 'allergens' => ['1', '3', '7'], 'sort_order' => 120],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Vepřová kotleta zapečená cibulí, anglickou slaninou a sýrem', 'amount' => 130, 'allergens' => ['1', '7'], 'sort_order' => 130],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Pečený filet ze štiky kapské s restovanými cherry rajčaty', 'amount' => 160, 'allergens' => ['4', '7'], 'sort_order' => 140],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Milánské špagety ze sójového masa s italským sýrem', 'amount' => null, 'allergens' => ['1', '6', '7', '8'], 'sort_order' => 150],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Vepřový plátek se sázeným vejcem', 'amount' => 130, 'allergens' => ['3', '7', '9', '10'], 'sort_order' => 160],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Pečený rybí filet po srbsku se zeleninovou směsí', 'amount' => 160, 'allergens' => ['4', '7', '9'], 'sort_order' => 170],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Zeleninové rizoto s brokolicí a uzeným sýrem', 'amount' => null, 'allergens' => ['7', '9'], 'sort_order' => 180],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Smažený květák', 'amount' => null, 'allergens' => ['1', '3', '7', '10', '12'], 'sort_order' => 190],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Vepřový gyros se zeleninou', 'amount' => 130, 'allergens' => ['1', '6', '7', '10'], 'sort_order' => 200],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Salát Caesar s grilovaným kuřecím masem', 'amount' => 130, 'allergens' => ['1', '3', '4', '7', '8', '10'], 'sort_order' => 210],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Karamelizovaný vepřový bůček na asijský způsob', 'amount' => 150, 'allergens' => ['1', '6', '9', '11'], 'sort_order' => 220],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Kebabčata z mletého masa', 'amount' => 150, 'allergens' => ['1', '3', '7', '10'], 'sort_order' => 230],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Uzené tofu s restovanou zeleninou', 'amount' => 120, 'allergens' => ['6', '9'], 'sort_order' => 240],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Kuřecí prsíčka', 'amount' => 130, 'allergens' => ['3', '7', '10'], 'sort_order' => 250],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Trhaná vepřová plec v BBQ marinádě', 'amount' => 140, 'allergens' => ['1', '3', '6', '7', '9', '10'], 'sort_order' => 260],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Lehký zeleninový salát s grilovaným hermelínem', 'amount' => null, 'allergens' => ['1', '3', '7'], 'sort_order' => 270],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Vepřový španělský ptáček', 'amount' => 140, 'allergens' => ['1', '3', '10'], 'sort_order' => 280],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Smažené květákové řízečky se sýrem cheddar', 'amount' => 150, 'allergens' => ['1', '3', '7', '10'], 'sort_order' => 290],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Špagety carbonara s pancettou a slaninou', 'amount' => null, 'allergens' => ['1', '3', '7'], 'sort_order' => 300],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Smažený karbanátek', 'amount' => 140, 'allergens' => ['1', '3', '7'], 'sort_order' => 310],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Krůtí kousky s teriyaki omáčkou a sezamem', 'amount' => 130, 'allergens' => ['1', '6', '9', '11'], 'sort_order' => 320],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Grilovaný steak z tuňáka', 'amount' => 160, 'allergens' => ['3', '4', '10'], 'sort_order' => 330],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Hovězí svratecký guláš s paprikou a žampiony', 'amount' => 130, 'allergens' => ['1', '3', '7', '9'], 'sort_order' => 340],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Pečené kuřecí stehno na česnekovém másle', 'amount' => 260, 'allergens' => ['1', '7'], 'sort_order' => 350],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Kuřecí řízek Corny v kukuřičných lupíncích', 'amount' => 130, 'allergens' => ['1', '3', '7'], 'sort_order' => 360],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Zapečené těstoviny s uzeným masem', 'amount' => 130, 'allergens' => ['1', '3', '7', '10'], 'sort_order' => 370],
            ['catalog_type' => 'hlavni-jidla', 'name' => 'Dukátové buchtičky s vanilkovým krémem', 'amount' => 130, 'allergens' => ['1', '3', '7'], 'sort_order' => 380],

            ['catalog_type' => 'prilohy', 'name' => 'Bramborová kaše', 'amount' => null, 'allergens' => ['7'], 'sort_order' => 10],
            ['catalog_type' => 'prilohy', 'name' => 'Pažitkové brambory', 'amount' => null, 'allergens' => [], 'sort_order' => 20],
            ['catalog_type' => 'prilohy', 'name' => 'Jasmínová rýže', 'amount' => null, 'allergens' => [], 'sort_order' => 30],
            ['catalog_type' => 'prilohy', 'name' => 'Smažené krokety', 'amount' => null, 'allergens' => ['1', '3', '7'], 'sort_order' => 40],
            ['catalog_type' => 'prilohy', 'name' => 'Smažené hranolky', 'amount' => null, 'allergens' => [], 'sort_order' => 50],
            ['catalog_type' => 'prilohy', 'name' => 'Bageta', 'amount' => null, 'allergens' => ['1'], 'sort_order' => 60],
            ['catalog_type' => 'prilohy', 'name' => 'Smažené bramborové plátky', 'amount' => null, 'allergens' => [], 'sort_order' => 70],
            ['catalog_type' => 'prilohy', 'name' => 'Houskový knedlík', 'amount' => null, 'allergens' => ['1', '3', '7'], 'sort_order' => 80],
            ['catalog_type' => 'prilohy', 'name' => 'Petrželové brambory', 'amount' => null, 'allergens' => ['7'], 'sort_order' => 90],
            ['catalog_type' => 'prilohy', 'name' => 'Vařené brambory', 'amount' => null, 'allergens' => [], 'sort_order' => 100],
            ['catalog_type' => 'prilohy', 'name' => 'Šťouchané brambory', 'amount' => null, 'allergens' => ['7'], 'sort_order' => 110],
            ['catalog_type' => 'prilohy', 'name' => 'Steakové hranolky', 'amount' => null, 'allergens' => [], 'sort_order' => 120],
            ['catalog_type' => 'prilohy', 'name' => 'Pečené brambory grenaille', 'amount' => null, 'allergens' => [], 'sort_order' => 130],
            ['catalog_type' => 'prilohy', 'name' => 'Čínské nudle', 'amount' => null, 'allergens' => ['1', '6'], 'sort_order' => 140],
            ['catalog_type' => 'prilohy', 'name' => 'Zeleninové hranolky s bramborami', 'amount' => null, 'allergens' => [], 'sort_order' => 150],
            ['catalog_type' => 'prilohy', 'name' => 'Coleslaw', 'amount' => null, 'allergens' => ['3', '7', '10'], 'sort_order' => 160],
            ['catalog_type' => 'prilohy', 'name' => 'Bramboráčky', 'amount' => null, 'allergens' => ['1', '3', '7', '9'], 'sort_order' => 170],
            ['catalog_type' => 'prilohy', 'name' => 'Rýže', 'amount' => null, 'allergens' => [], 'sort_order' => 180],
            ['catalog_type' => 'prilohy', 'name' => 'Chléb', 'amount' => null, 'allergens' => ['1'], 'sort_order' => 190],
            ['catalog_type' => 'prilohy', 'name' => 'Kozí rohy', 'amount' => null, 'allergens' => [], 'sort_order' => 200],
            ['catalog_type' => 'prilohy', 'name' => 'Hrachová kaše', 'amount' => null, 'allergens' => ['1', '3', '7', '10'], 'sort_order' => 210],

            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Kyselý okurek', 'amount' => null, 'allergens' => [], 'sort_order' => 10],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Dressing', 'amount' => null, 'allergens' => ['3', '7', '10'], 'sort_order' => 20],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Bylinkový dressing', 'amount' => null, 'allergens' => ['3', '7', '10'], 'sort_order' => 30],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Dressing z hrubozrnné hořčice', 'amount' => null, 'allergens' => ['10'], 'sort_order' => 40],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Koprová omáčka', 'amount' => null, 'allergens' => ['1', '3', '7', '9'], 'sort_order' => 50],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Omáčka z lesního ovoce', 'amount' => null, 'allergens' => [], 'sort_order' => 60],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Krém ze zakysané smetany a tvarohu', 'amount' => null, 'allergens' => ['7'], 'sort_order' => 70],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Zeleninový salát', 'amount' => null, 'allergens' => [], 'sort_order' => 80],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Zeleninový listový salát', 'amount' => null, 'allergens' => [], 'sort_order' => 90],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Pepřová omáčka', 'amount' => null, 'allergens' => ['1', '7', '9', '10'], 'sort_order' => 100],
            ['catalog_type' => 'omacky-a-ostatni', 'name' => 'Houbové ragú', 'amount' => null, 'allergens' => ['1', '7', '9'], 'sort_order' => 110],

            ['catalog_type' => 'pizza', 'name' => 'Pizza Margherita', 'amount' => null, 'allergens' => ['1', '7'], 'sort_order' => 10],
            ['catalog_type' => 'pizza', 'name' => 'Pizza Prosciutto', 'amount' => null, 'allergens' => ['1', '7'], 'sort_order' => 20],

            ['catalog_type' => 'grill', 'name' => 'Grilovaná vepřová kotleta', 'amount' => 200, 'allergens' => ['1', '7', '9', '10'], 'sort_order' => 10],
            ['catalog_type' => 'grill', 'name' => 'Grilovaný kuřecí prsní steak', 'amount' => 200, 'allergens' => ['1', '7', '9', '10'], 'sort_order' => 20],
        ];
    }
}
