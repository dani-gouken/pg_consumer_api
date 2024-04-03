<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.scss')
    @livewireStyles
</head>

<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto lg:px-0 box-border px-6 max-w-screen">
        @include('partials.navigation')
        <div class="rounded-3xl ring-primary-600 ring-1 shadow overflow-hidden bg-white">
            <div class="px-6 py-8">
                @yield('body')
            </div>
        </div>
        @include('partials.footer')
    </div>
    @livewireScripts
    @vite('resources/js/app.js')
</body>

</html>
