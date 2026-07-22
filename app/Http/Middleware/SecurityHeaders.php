<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('security_headers.enabled', true)) {
            return $response;
        }

        /** @var array<string, string> $headers */
        $headers = config('security_headers.headers', []);

        foreach ($headers as $name => $value) {
            if (is_string($value) && $value !== '') {
                $response->headers->set($name, $value);
            }
        }

        $response->headers->remove('X-Powered-By');

        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        return $response;
    }
}
