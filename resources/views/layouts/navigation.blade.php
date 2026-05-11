<nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">

            {{-- ── Logo ── --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-gray-800 dark:text-white">{{ __('messages.app_name') }}</span>
            </a>

            {{-- ── Right side controls ── --}}
            <div class="flex items-center gap-2">

                {{-- Language toggle --}}
                <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                    <a href="{{ route('locale.switch', 'en') }}"
                       class="px-2.5 py-1 text-xs font-semibold rounded-md transition
                              {{ app()->getLocale() === 'en'
                                  ? 'bg-white dark:bg-gray-700 text-indigo-700 dark:text-indigo-400 shadow-sm'
                                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                        EN
                    </a>
                    <a href="{{ route('locale.switch', 'ja') }}"
                       class="px-2.5 py-1 text-xs font-semibold rounded-md transition
                              {{ app()->getLocale() === 'ja'
                                  ? 'bg-white dark:bg-gray-700 text-indigo-700 dark:text-indigo-400 shadow-sm'
                                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                        JA
                    </a>
                </div>

                {{-- ── Dark mode toggle ── --}}
                <div x-data="{ dark: document.documentElement.classList.contains('dark') }">
                    <button
                        @click="
                            dark = !dark;
                            document.documentElement.classList.toggle('dark', dark);
                            localStorage.setItem('theme', dark ? 'dark' : 'light');
                        "
                        :aria-label="dark ? '{{ __('messages.switch_light') }}' : '{{ __('messages.switch_dark') }}'"
                        class="w-9 h-9 flex items-center justify-center rounded-lg
                               text-gray-500 dark:text-gray-400
                               hover:bg-gray-100 dark:hover:bg-gray-800
                               hover:text-gray-700 dark:hover:text-gray-200
                               transition">
                        {{-- Moon — visible in light mode, click to go dark --}}
                        <svg x-show="!dark" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        {{-- Sun — visible in dark mode, click to go light --}}
                        <svg x-show="dark" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                </div>

                {{-- User dropdown --}}
                <div class="relative" x-data="{ open: false }">

                    {{-- Trigger --}}
                    <button
                        @click="open = !open"
                        @keydown.escape.window="open = false"
                        type="button"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium
                               text-gray-600 dark:text-gray-300
                               hover:bg-gray-100 dark:hover:bg-gray-800
                               focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">

                        <span class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900
                                     text-indigo-700 dark:text-indigo-300
                                     text-xs font-bold flex items-center justify-center uppercase">
                            {{ mb_substr(Auth::user()->name, 0, 1) }}
                        </span>

                        <span>{{ Auth::user()->name }}</span>

                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 transition-transform duration-200"
                             :class="{ 'rotate-180': open }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown panel --}}
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-52 bg-white dark:bg-gray-800
                               rounded-xl shadow-lg border border-gray-100 dark:border-gray-700
                               py-1 z-50"
                        style="display:none">

                        {{-- User info --}}
                        <div class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                {{ Auth::user()->email }}
                            </p>
                        </div>

                        {{-- Profile --}}
                        <a href="{{ route('profile.edit') }}"
                           @click="open = false"
                           class="flex items-center gap-2.5 px-4 py-2 text-sm
                                  text-gray-600 dark:text-gray-300
                                  hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ __('messages.profile') }}
                        </a>

                        {{-- Download CSV --}}
                        <a href="{{ route('expenses.export') }}"
                           @click="open = false"
                           class="flex items-center gap-2.5 px-4 py-2 text-sm
                                  text-gray-600 dark:text-gray-300
                                  hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            {{ __('messages.download_csv') }}
                        </a>

                        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-left
                                       text-red-600 dark:text-red-400
                                       hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                {{ __('messages.log_out') }}
                            </button>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</nav>
