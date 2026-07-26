<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">


        <!-- styles -->
        <link rel="stylesheet" href="https://use.typekit.net/jrr4gcr.css">

        <!-- Favicon and touch icons -->
        <link rel="shortcut icon" href="{{ asset('img/favicon/favicon.svg')}}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('img/favicon/apple-touch-icon-144.svg')}}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('img/favicon/apple-touch-icon-114.svg')}}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('img/favicon/apple-touch-icon-72.svg')}}">
        <link rel="apple-touch-icon" href="{{ asset('img/favicon/apple-touch-icon-57.svg')}}">

        @routes
        @vite(['resources/js/front/app.ts'])
        @inertiaHead
    </head>
    <body class="antialiased">
        @inertia
    </body>
</html>
