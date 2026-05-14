<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Log in') }} · {{ config('app.name', 'Expense Tracker') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Apply dark mode before first paint to avoid flash --}}
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

    <style>
        /* Subtle dot-grid texture overlaid on the gradient background */
        .dot-grid {
            background-image: radial-gradient(circle, rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        /* Soft glow behind the card */
        .auth-glow::before {
            content: '';
            position: absolute;
            inset: -40px;
            background: radial-gradient(ellipse at center, rgba(99, 102, 241, 0.25), transparent 70%);
            filter: blur(40px);
            z-index: -1;
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 dark:text-gray-100">

    {{-- ── Full-screen background: gradient + dot grid ───────────────────── --}}
    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6
                bg-gradient-to-br from-indigo-50 via-white to-violet-50
                dark:from-gray-950 dark:via-indigo-950 dark:to-violet-950 relative overflow-hidden">

        {{-- Decorative orbs (light + dark variants) --}}
        <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-indigo-400/20 dark:bg-indigo-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-violet-400/20 dark:bg-violet-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute inset-0 dot-grid opacity-40 dark:opacity-100 pointer-events-none"></div>

        {{-- ── Auth card ─────────────────────────────────────────────────── --}}
        <div class="relative w-full max-w-md auth-glow">
            <div class="relative bg-white/90 dark:bg-gray-900/70 backdrop-blur-xl
                        rounded-2xl shadow-xl shadow-indigo-900/10 dark:shadow-black/40
                        ring-1 ring-gray-200/60 dark:ring-white/10
                        p-8 sm:p-10">

                {{-- Brand mark --}}
                <div class="flex flex-col items-center mb-8">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl
                                bg-gradient-to-br from-indigo-600 to-violet-600
                                shadow-lg shadow-indigo-600/40 mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 7h6m-6 4h6m-6 4h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ __('Welcome back') }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Sign in to continue tracking your expenses.') }}
                    </p>
                </div>

                {{-- Session status (e.g., "We have emailed your password reset link!") --}}
                @if (session('status'))
                    <div class="mb-4 px-4 py-3 rounded-lg text-sm
                                bg-green-50 dark:bg-green-900/30
                                border border-green-200 dark:border-green-800
                                text-green-800 dark:text-green-300">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- ── Form ──────────────────────────────────────────────── --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ __('Email') }}
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 dark:text-gray-500 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input id="email" name="email" type="email" required autofocus autocomplete="username"
                                   value="{{ old('email') }}"
                                   placeholder="you@example.com"
                                   class="w-full pl-10 pr-3 py-2.5 rounded-lg text-sm transition-all
                                          bg-white dark:bg-gray-800/60
                                          text-gray-900 dark:text-gray-100
                                          placeholder-gray-400 dark:placeholder-gray-600
                                          border focus:outline-none focus:ring-4 focus:ring-indigo-500/20
                                          {{ $errors->has('email')
                                              ? 'border-red-400 dark:border-red-700 focus:border-red-500'
                                              : 'border-gray-300 dark:border-gray-700 focus:border-indigo-500' }}">
                        </div>
                        @foreach($errors->get('email') as $message)
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @endforeach
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ __('Password') }}
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 dark:text-gray-500 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="w-full pl-10 pr-11 py-2.5 rounded-lg text-sm transition-all
                                          bg-white dark:bg-gray-800/60
                                          text-gray-900 dark:text-gray-100
                                          placeholder-gray-400 dark:placeholder-gray-600
                                          border focus:outline-none focus:ring-4 focus:ring-indigo-500/20
                                          {{ $errors->has('password')
                                              ? 'border-red-400 dark:border-red-700 focus:border-red-500'
                                              : 'border-gray-300 dark:border-gray-700 focus:border-indigo-500' }}">
                            <button type="button"
                                    onclick="const i=document.getElementById('password');i.type=i.type==='password'?'text':'password';this.querySelectorAll('svg').forEach(s=>s.classList.toggle('hidden'))"
                                    class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                                    aria-label="{{ __('Toggle password visibility') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @foreach($errors->get('password') as $message)
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @endforeach
                    </div>

                    {{-- Remember + Forgot --}}
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                            <input id="remember_me" type="checkbox" name="remember"
                                   class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600
                                          focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-0 bg-white dark:bg-gray-800">
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    {{-- Primary CTA --}}
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl
                                   bg-gradient-to-r from-indigo-600 to-violet-600
                                   hover:from-indigo-700 hover:to-violet-700
                                   active:from-indigo-800 active:to-violet-800
                                   text-white text-sm font-semibold
                                   shadow-lg shadow-indigo-600/30 dark:shadow-indigo-900/50
                                   hover:scale-[1.02] active:scale-[0.99]
                                   transition-all duration-200">
                        {{ __('Sign in') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </form>

                {{-- ── Divider ───────────────────────────────────────────── --}}
                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-800"></div>
                    <span class="text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 font-medium">
                        {{ __('or') }}
                    </span>
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-800"></div>
                </div>

                {{-- ── Secondary: demo login ─────────────────────────────── --}}
                <a href="{{ url('/demo-login') }}"
                   class="flex items-center justify-center gap-2 w-full px-5 py-2.5 rounded-xl
                          bg-white dark:bg-gray-900/60
                          border border-gray-300 dark:border-gray-700
                          hover:border-indigo-400 dark:hover:border-indigo-600
                          text-gray-700 dark:text-gray-200 text-sm font-semibold
                          transition-colors">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    {{ __('Log in as Demo User') }}
                </a>

                {{-- ── Footer: sign up link ──────────────────────────────── --}}
                @if (Route::has('register'))
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
                        {{ __("Don't have an account?") }}
                        <a href="{{ route('register') }}"
                           class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                            {{ __('Sign up') }}
                        </a>
                    </p>
                @endif
            </div>

            {{-- Back-to-home link below the card --}}
            <p class="text-center text-xs text-gray-400 dark:text-gray-600 mt-6">
                <a href="{{ url('/') }}" class="hover:text-gray-600 dark:hover:text-gray-400 transition-colors">
                    ← {{ __('Back to home') }}
                </a>
            </p>
        </div>

    </div>
</body>
</html>
