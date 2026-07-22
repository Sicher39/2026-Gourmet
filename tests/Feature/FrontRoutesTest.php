<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\GdprController;
use App\Http\Controllers\Front\SitemapController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FrontRoutesTest extends TestCase
{
    public function test_sitemap_route_points_to_controller(): void
    {
        $route = Route::getRoutes()->getByName('sitemap');

        $this->assertNotNull($route, 'The sitemap named route must exist.');
        $this->assertSame(SitemapController::class, $route->getControllerClass());
    }

    public function test_contact_route_points_to_controller(): void
    {
        $route = Route::getRoutes()->getByName('front.contact');

        $this->assertNotNull($route, 'The front.contact named route must exist.');
        $this->assertSame(ContactController::class, $route->getControllerClass());
    }

    public function test_gdpr_route_points_to_controller(): void
    {
        $route = Route::getRoutes()->getByName('front.gdpr');

        $this->assertNotNull($route, 'The front.gdpr named route must exist.');
        $this->assertSame(GdprController::class, $route->getControllerClass());
    }

    public function test_sitemap_returns_xml_with_known_named_route_url(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $contactUrl = route('front.contact');
        $this->assertStringContainsString(
            htmlspecialchars($contactUrl, ENT_XML1 | ENT_COMPAT, 'UTF-8'),
            $response->getContent(),
        );
    }

    public function test_contact_renders_inertia_component_with_expected_fallback_props(): void
    {
        $response = $this->get('/kontakt');

        $response->assertOk();

        $response->assertInertia(function ($page): void {
            $page->component('Contact', false);
        });

        $props = $response->inertiaProps();

        $this->assertArrayHasKey('companyInfo', $props);
        $this->assertArrayHasKey('companyProfile', $props);
    }

    public function test_gdpr_renders_inertia_component_with_expected_props(): void
    {
        $response = $this->get('/ochrana-osobnich-udaju');

        $response->assertOk();

        $response->assertInertia(function ($page): void {
            $page->component('Gdpr', false);
        });

        $props = $response->inertiaProps();

        $this->assertArrayHasKey('companyProfile', $props);
        $this->assertArrayHasKey('processingPurposes', $props);
        $this->assertArrayHasKey('technicalCookies', $props);
    }

    public function test_contact_route_is_accessible_via_named_route(): void
    {
        $response = $this->get(route('front.contact'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Contact', false));
    }

    public function test_gdpr_route_is_accessible_via_named_route(): void
    {
        $response = $this->get(route('front.gdpr'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Gdpr', false));
    }
}
