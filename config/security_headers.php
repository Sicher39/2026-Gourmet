<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | This file configures the HTTP security headers added by the
    | SecurityHeaders middleware. Set SECURITY_HEADERS_ENABLED=false
    | to disable all header injection (useful for local development).
    |
    | Each header value can be set via the corresponding ENV variable.
    | Empty-string values are treated as "do not send this header".
    |
    */

    'enabled' => (bool) env('SECURITY_HEADERS_ENABLED', true),

    'headers' => [

        'X-Content-Type-Options' => env('SECURITY_HEADER_X_CONTENT_TYPE_OPTIONS', 'nosniff'),

        'Referrer-Policy' => env('SECURITY_HEADER_REFERRER_POLICY', 'strict-origin-when-cross-origin'),

        'X-Frame-Options' => env('SECURITY_HEADER_X_FRAME_OPTIONS', 'SAMEORIGIN'),

        'Cross-Origin-Opener-Policy' => env('SECURITY_HEADER_CROSS_ORIGIN_OPENER_POLICY', 'same-origin'),

        'Content-Security-Policy' => env('SECURITY_HEADER_CONTENT_SECURITY_POLICY', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'"),

        'Strict-Transport-Security' => env('SECURITY_HEADER_STRICT_TRANSPORT_SECURITY', 'max-age=31536000; includeSubDomains'),

        'Permissions-Policy' => env('SECURITY_HEADER_PERMISSIONS_POLICY', 'accelerometer=(), autoplay=(), camera=(), display-capture=(), encrypted-media=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), midi=(), payment=(), picture-in-picture=(), publickey-credentials-get=(), usb=(), xr-spatial-tracking=()'),

    ],

];
