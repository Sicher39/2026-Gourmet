<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/security-headers-test', static fn () => response('ok')->header('X-Powered-By', 'PHP/8.4.12'));
    }

    public function test_it_adds_configured_security_headers_to_responses(): void
    {
        config()->set('security_headers.enabled', true);

        $response = $this->get('/security-headers-test');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->assertHeader('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'");
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->assertHeader('Permissions-Policy', 'accelerometer=(), autoplay=(), camera=(), display-capture=(), encrypted-media=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), midi=(), payment=(), picture-in-picture=(), publickey-credentials-get=(), usb=(), xr-spatial-tracking=()');
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_it_does_not_modify_responses_when_security_headers_are_disabled(): void
    {
        config()->set('security_headers.enabled', false);

        $response = $this->get('/security-headers-test');

        $response->assertOk();
        $response->assertHeader('X-Powered-By', 'PHP/8.4.12');
        $this->assertFalse($response->headers->has('X-Content-Type-Options'));
    }
}
