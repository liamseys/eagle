<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ !empty($generalSettings->app_name) ? $generalSettings->app_name : config('app.name') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ !empty($generalSettings->branding_favicon) ? Storage::url($generalSettings->branding_favicon) : asset('favicon.png') }}" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=lexend:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body {{ $attributes->merge(['class' => 'font-sans text-gray-900 antialiased']) }}>
        {{ $slot }}
    </body>
</html>
