<!DOCTYPE html>
<html lang="cs-CZ" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php($seo = \App\Models\SeoPage::resolveForRequest(request()))
        <title>{{ $seo['title'] }}</title>
        <meta name="description" content="{{ $seo['description'] }}">
        <meta name="robots" content="{{ $seo['robots'] }}">
        <link rel="canonical" href="{{ $seo['canonical'] }}">
        <meta property="og:type" content="{{ $seo['ogType'] }}">
        <meta property="og:title" content="{{ $seo['ogTitle'] }}">
        <meta property="og:description" content="{{ $seo['ogDescription'] }}">
        <meta property="og:url" content="{{ $seo['ogUrl'] }}">
        <meta property="og:locale" content="{{ $seo['ogLocale'] }}">
        <meta property="og:site_name" content="{{ $seo['ogSiteName'] }}">
        @if (!empty($seo['ogImage']))
            <meta property="og:image" content="{{ $seo['ogImage'] }}">
        @endif
        <meta name="twitter:card" content="{{ $seo['twitterCard'] }}">
        <meta name="twitter:title" content="{{ $seo['twitterTitle'] }}">
        <meta name="twitter:description" content="{{ $seo['twitterDescription'] }}">
        @if (!empty($seo['twitterImage']))
            <meta name="twitter:image" content="{{ $seo['twitterImage'] }}">
        @endif
        @if (!empty($seo['structuredData']))
            <script type="application/ld+json">{!! json_encode($seo['structuredData'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endif

        <!-- styles -->
        <link rel="stylesheet" href="https://use.typekit.net/jrr4gcr.css">

        <!-- Favicon and touch icons -->
        <link rel="shortcut icon" href="{{ asset('img/favicon/favicon.svg')}}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('img/favicon/apple-touch-icon-144.svg')}}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('img/favicon/apple-touch-icon-114.svg')}}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('img/favicon/apple-touch-icon-72.svg')}}">
        <link rel="apple-touch-icon" href="{{ asset('img/favicon/apple-touch-icon-57.svg')}}">

        @routes('frontend')
        @vite(['resources/js/front/app.ts'])
        @inertiaHead
    </head>
    <body class="antialiased">
        @inertia
    </body>
</html>
