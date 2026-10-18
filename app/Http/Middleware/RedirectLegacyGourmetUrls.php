<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Přesměruje pouze explicitně uvedené staré cesty na příslušnou novou stránku.
 * Nezasahuje do existujících rout '/', '/gourmet-ponavka' a '/gourmet-u-vankovky'.
 * Musí být registrován jako GLOBÁLNÍ middleware, aby zachytil i jinak neexistující cesty.
 */
final class RedirectLegacyGourmetUrls
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $hosts = config('gourmet_legacy_redirects.hosts', []);
        $host = strtolower($request->getHost());
        $hostRedirects = $hosts[$host] ?? null;

        if ($hostRedirects === null) {
            return $next($request);
        }

        // Pro stejný obsah sjednotíme '/catering/' i '/catering'.
        $path = '/' . trim($request->getPathInfo(), '/');
        $targetPath = $hostRedirects[$path] ?? null;

        if ($targetPath === null) {
            return $next($request);
        }

        $targetHost = rtrim((string) config('gourmet_legacy_redirects.target_host'), '/');

        // Nepřenášíme historické WordPress query parametry.
        // Anchor (#...) je součástí hlavičky Location, nikoli vstupního HTTP požadavku.
        return redirect()->away($targetHost . $targetPath, 301);
    }
}
