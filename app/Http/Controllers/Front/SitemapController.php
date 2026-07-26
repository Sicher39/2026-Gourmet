<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\SeoPage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $entries = [
            ['path' => '/', 'route' => 'front.index'],
            ['path' => '/napojovy-listek', 'route' => 'front.drinkMenu'],
            ['path' => '/jidelni-listek', 'route' => 'front.foodMenu'],
            ['path' => '/kontakt', 'route' => 'front.contact'],
            ['path' => '/podminky-rezervace', 'route' => 'front.reservationTerms'],
            ['path' => '/ochrana-osobnich-udaju', 'route' => 'front.gdpr'],
            ['path' => '/zasady-cookies', 'route' => 'front.cookies'],
            ['path' => '/restaurant/reservation', 'route' => 'restaurant.reservation'],
        ];

        $seoPagesByPath = [];

        if (Schema::hasTable('seo_pages')) {
            $seoPagesByPath = SeoPage::query()
                ->whereIn('path', array_column($entries, 'path'))
                ->get(['path', 'updated_at'])
                ->keyBy('path')
                ->all();
        }

        $escapeXml = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');

        $urlsXml = collect($entries)
            ->map(function (array $entry) use ($seoPagesByPath, $escapeXml): string {
                $path = $entry['path'];
                $routeName = $entry['route'];
                $location = $routeName !== null ? route($routeName) : url($path);

                /** @var SeoPage|null $seoPage */
                $seoPage = $seoPagesByPath[$path] ?? null;
                $lastmod = $entry['lastmod'] ?? $seoPage?->updated_at?->toAtomString() ?? now()->toAtomString();

                return sprintf(
                    "    <url>\n        <loc>%s</loc>\n        <lastmod>%s</lastmod>\n    </url>",
                    $escapeXml($location),
                    $escapeXml($lastmod),
                );
            })
            ->implode("\n");

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{$urlsXml}
</urlset>
XML;

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
