<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Expense Tracker') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Apply dark mode before first paint --}}
    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-md">

        {{-- ── Hero ────────────────────────────────────────────────────── --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-600/30 mb-5">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 7h6m-6 4h6m-6 4h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">
                {{ config('app.name', 'Expense Tracker') }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Multi-currency expense tracking, powered by AI receipt scanning.
            </p>
        </div>

        @if(session('error'))
            <div class="mb-4 px-4 py-3 rounded-xl text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        @auth
            {{-- Already logged in — single CTA to the dashboard --}}
            <a href="{{ url('/dashboard') }}"
               class="flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl
                      bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
                      text-white text-sm font-semibold shadow-sm transition-colors">
                Go to Dashboard
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        @else
            {{-- ── Three CTAs ─────────────────────────────────────────── --}}
            <div class="space-y-3">

                {{-- 1. Prominent: Demo login --}}
                <a href="{{ url('/demo-login') }}"
                   class="group flex items-center justify-between gap-3 w-full px-5 py-4 rounded-xl
                          bg-gradient-to-r from-indigo-600 to-violet-600
                          hover:from-indigo-700 hover:to-violet-700
                          active:from-indigo-800 active:to-violet-800
                          text-white shadow-lg shadow-indigo-600/30 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white/15 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold">Log in as Demo User</p>
                            <p class="text-xs text-indigo-100/90">Skip the form — explore instantly</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 opacity-70 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                {{-- Separator --}}
                <div class="flex items-center gap-3 py-1">
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-800"></div>
                    <span class="text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 font-medium">or</span>
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-800"></div>
                </div>

                {{-- 2. Standard: Log in --}}
                <a href="{{ route('login') }}"
                   class="flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl
                          bg-white dark:bg-gray-900
                          border border-gray-300 dark:border-gray-700
                          hover:border-gray-400 dark:hover:border-gray-600
                          text-gray-700 dark:text-gray-200 text-sm font-semibold
                          shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Log in
                </a>

                {{-- 3. Standard: Sign up --}}
                @if(Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl
                              bg-white dark:bg-gray-900
                              border border-gray-300 dark:border-gray-700
                              hover:border-gray-400 dark:hover:border-gray-600
                              text-gray-700 dark:text-gray-200 text-sm font-semibold
                              shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Sign up
                    </a>
                @endif

            </div>
        @endauth

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-400 dark:text-gray-600 mt-8">
            Built for the hackathon · Laravel {{ \Illuminate\Foundation\Application::VERSION }}
        </p>

    </div>

</body>
</html>
