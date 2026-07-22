<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HomepagePhotoSection;
use Illuminate\Database\Seeder;

class HomepagePhotoSectionSeeder extends Seeder
{
    /**
     * Canonical photo sections keyed by handle.
     *
     * Images use root‑relative paths so they survive storage‑disk migrations
     * and work directly with the frontend IndexPhotoBlock component.
     */
    private const SECTIONS = [
        [
            'handle' => 'food',
            'name' => 'Foto sekce – jídlo',
            'is_active' => true,
            'sort_order' => 10,
            'header' => '…něco k jídlu',
            'note' => 'Vaříme české klasiky i speciality inspirované zahraniční kuchyní. Používáme kvalitní suroviny a menu pravidelně obměňujeme, aby bylo pořád co objevovat.',
            'image_one_path' => '/img/bg/small/1.webp',
            'image_two_path' => '/img/bg/small/2.webp',
            'image_three_path' => '/img/bg/small/3.webp',
            'button_label' => 'jídelní lístek',
            'button_route_name' => 'front.foodMenu',
            'button_url' => null,
        ],
        [
            'handle' => 'drinks',
            'name' => 'Foto sekce – pití',
            'is_active' => true,
            'sort_order' => 20,
            'header' => '…něco k pití',
            'note' => 'U nás se pivu věnuje pozornost, kterou si zaslouží. Hladinka, šnyt, mlíko nebo čochtan? Každý půllitr musí vypadat i chutnat správně.',
            'image_one_path' => '/img/bg/small/4.webp',
            'image_two_path' => '/img/bg/small/5.webp',
            'image_three_path' => '/img/bg/small/6.webp',
            'button_label' => 'nápojový lístek',
            'button_route_name' => 'front.drinkMenu',
            'button_url' => null,
        ],
        [
            'handle' => 'events',
            'name' => 'Foto sekce – akce',
            'is_active' => true,
            'sort_order' => 30,
            'header' => 'Protože nejlepší akce nevznikají podle šablony.',
            'note' => "V létě si vychutnáte posezení na zahrádce, během roku u nás můžete uspořádat narozeniny, firemní večírek nebo večer s partou přátel.\nVšechno řešíme individuálně.",
            'image_one_path' => '/img/bg/small/7.webp',
            'image_two_path' => '/img/bg/small/8.webp',
            'image_three_path' => '/img/bg/small/9.webp',
            'button_label' => 'rezervovat stůl',
            'button_route_name' => 'restaurant.reservation',
            'button_url' => null,
        ],
    ];

    public function run(): void
    {
        foreach (self::SECTIONS as $section) {
            HomepagePhotoSection::query()->updateOrCreate(
                ['handle' => $section['handle']],
                $section,
            );
        }
    }
}
