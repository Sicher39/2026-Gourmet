<?php

declare(strict_types=1);

// Mapování původních funkčních WordPress URL (HTTP 200) na nové stránky.
// Klíče cest jsou bez koncového lomítka; middleware sjednotí obě varianty.
// Kořenové cesty nejsou součástí mapy, protože je obsluhují současné stránky poboček.
return [
    'target_host' => 'https://gourmetrestaurant.cz',
    'hosts' => [
        'gourmetrestaurant.cz' => [
            '/catering' => '/#catering',
            '/fresh-juice' => '/',
            '/galerie' => '/',
            '/gallery/dezerty' => '/',
            '/gallery/jidlo' => '/',
            '/home' => '/',
            '/kafe-gourmet' => '/',
            '/kavarna' => '/',
            '/kontakt' => '/#kontakt',
            '/novinky' => '/',
            '/poledni-menu' => '/',
            '/rozovz' => '/#rozvoz',
            '/rubrika/kavarna' => '/',
            '/stitky/wine-2' => '/',
        ],
        'ponavka.gourmetrestaurant.cz' => [
            '/catering' => '/gourmet-ponavka',
            '/catering/50082604_136563043944561_5526687921341464576_n' => '/#catering',
            '/catering/46521765_703732426672228_2446241697663811584_n' => '/gourmet-ponavka',
            '/catering/49848547_2292825444309256_8682177888084033536_n' => '/gourmet-ponavka',
            '/catering/50023982_1266462663491705_7647739838090706944_n' => '/gourmet-ponavka',
            '/catering/69743512_2477948639196993_7387637121156120576_n' => '/gourmet-ponavka',
            '/catering/88009056_221840788939344_4537231836933259264_n' => '/gourmet-ponavka',
            '/catering/88064008_222783188855682_500565317223710720_n' => '/gourmet-ponavka',
            '/catering/88077011_151854269266020_7551391395085287424_n' => '/gourmet-ponavka',
            '/catering/88079603_674481119960298_6572460179575537664_n' => '/gourmet-ponavka',
            '/catering/88155435_130216998409124_4074526780977840128_n' => '/gourmet-ponavka',
            '/catering/88155562_293188184991118_3710242721676394496_n' => '/gourmet-ponavka',
            '/catering/88237731_286939012280643_3634522001571315712_n' => '/gourmet-ponavka',
            '/catering/88240663_197202808302794_7689968145523539968_n' => '/gourmet-ponavka',
            '/catering/88278859_184890469599476_4276249961668018176_n' => '/gourmet-ponavka',
            '/catering/88310963_620051002112487_3296226557449732096_n' => '/gourmet-ponavka',
            '/catering/88346912_616491205856031_4097047106986967040_n' => '/gourmet-ponavka',
            '/catering/89114048_3365095996853054_5138195485034545152_n' => '/gourmet-ponavka',
            '/dorty' => '/gourmet-ponavka#kavarna',
            '/galerie' => '/gourmet-ponavka',
            '/gallery/dezerty' => '/gourmet-ponavka#kavarna',
            '/gallery/jidlo' => '/gourmet-ponavka',
            '/gallery/obcerstveni' => '/gourmet-ponavka',
            '/kafe-gourmet' => '/gourmet-ponavka#kavarna',
            '/kavarna' => '/gourmet-ponavka#kavarna',
            '/kontakt' => '/gourmet-ponavka#kontakt',
            '/novinky' => '/gourmet-ponavka',
            '/poledni-menu' => '/gourmet-ponavka#tydenni-menu',
            '/rozovz' => '/gourmet-ponavka',
            '/rubrika/kavarna' => '/gourmet-ponavka#kavarna',
            '/stitky/wine-2' => '/gourmet-ponavka',
        ],
        'uvankovky.gourmetrestaurant.cz' => [
            '/nove-otevreno' => '/gourmet-u-vankovky',
            '/rubrika/kavarna' => '/gourmet-u-vankovky#kavarna',
            '/rubrika/nezarazene' => '/gourmet-u-vankovky',
        ],
    ],
];
