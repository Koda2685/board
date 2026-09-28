<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-y-auto font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.12),transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(15,23,42,0.12),transparent_30%)] px-4 py-0 sm:px-6">
            <div class="mx-auto w-full max-w-5xl">
                <div class="mb-0 flex items-center justify-center">
                    <a href="/" class="-mt-2 mt-1 flex flex-col items-center gap-0 text-center leading-none">
                        <img
                            src="{{ asset('logo.png') }}"
                            alt="Drivers United logo"
                            style="width: 7rem !important; height: 7rem !important; object-fit: contain; display: block;"
                            class="drop-shadow-[0_8px_16px_rgba(15,23,42,0.18)]"
                        />
                        <div>
                            <div class="text-[0.5rem] font-semibold uppercase tracking-[0.22em] text-[#f97316]">Drivers United</div>
                            <div class="text-sm font-black tracking-wide text-[#0f172a]">Governance</div>
                        </div>
                    </a>
                </div>

                <div class="mx-auto w-full max-w-md overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white/90 px-5 py-4 shadow-[0_18px_44px_rgba(15,23,42,0.12)] backdrop-blur-sm sm:px-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
