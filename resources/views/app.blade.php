<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light" data-pwa="true">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Viewport -->
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Vite Assets (will be injected here) -->
        @vite(['resources/js/app.js', 'resources/css/app.css'])

        <!-- Inertia Head -->
        @inertiaHead

        <!-- Ziggy Routes -->
        @routes
    </head>

    <body>
        <!-- Inertia Mount Point -->
        @inertia
    </body>
</html>
