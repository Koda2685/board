<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Drivers United Governance') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(249,115,22,0.12),transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(15,23,42,0.12),transparent_30%)]">
            <header class="mx-auto max-w-6xl px-6 pt-0 pb-0">
                <div class="mb-0 flex items-center justify-between gap-2">
                    <div class="-mt-2 flex flex-col items-center leading-none">
                        <img src="{{ asset('logo5.png') }}" alt="Drivers United logo" class="h-[20rem] w-[20rem] object-contain drop-shadow-[0_8px_16px_rgba(15,23,42,0.18)]" />
                        <div class="mt-0 text-center leading-none">
                            <p class="text-[0.52rem] font-semibold uppercase tracking-[0.22em] text-[#f97316]">Drivers United</p>
                            <h1 class="text-base font-bold text-slate-900">Governance</h1>
                        </div>
                    </div>

                    @if (Route::has('login'))
                        <nav class="ml-auto flex items-center gap-3">
                            <a href="{{ route('login') }}" class="brand-button-secondary">Log in</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="brand-button-primary">Register</a>
                            @endif
                        </nav>
                    @endif
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-6 py-6 lg:py-8">
                <div class="grid items-center gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <div>
                        <span class="status-pill">Secure governance portal</span>

                        <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                            Governance records, decisions, and document access in one place.
                        </h2>

                        <p class="mt-4 max-w-xl text-base text-slate-600">
                            Drivers United Governance brings together resolutions, minutes, supporting documents, and reporting in a secure portal designed for timely internal review and public access where needed.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-4">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="brand-button-primary">Access portal</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="brand-button-secondary">Register</a>
                                @endif
                            @endif

                            <a href="#overview" class="brand-button-secondary">View overview</a>
                        </div>

                        <div id="overview" class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm">
                                <div class="text-2xl font-black text-slate-900">24/7</div>
                                <div class="mt-1 text-sm text-slate-600">Secure access</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm">
                                <div class="text-2xl font-black text-slate-900">100%</div>
                                <div class="mt-1 text-sm text-slate-600">Document traceability</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm">
                                <div class="text-2xl font-black text-slate-900">Live</div>
                                <div class="mt-1 text-sm text-slate-600">Governance updates</div>
                            </div>
                        </div>
                    </div>

                    <div class="brand-panel relative overflow-hidden rounded-[2rem] border border-slate-200/10 p-6 shadow-brand">
                        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-orange-400/20 blur-3xl"></div>
                        <div class="absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-sky-400/20 blur-3xl"></div>

                        <div class="relative rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-200">Overview</span>
                                <span class="rounded-full bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-200">Active</span>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div class="rounded-2xl bg-white/8 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Board resolutions</p>
                                    <p class="mt-2 text-3xl font-black text-white">18</p>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-slate-900/30 p-4">
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Minutes</p>
                                        <p class="mt-2 text-2xl font-bold text-white">9</p>
                                    </div>
                                    <div class="rounded-2xl bg-orange-500/15 p-4">
                                        <p class="text-xs uppercase tracking-[0.2em] text-orange-100">Supporting files</p>
                                        <p class="mt-2 text-2xl font-bold text-white">27</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
