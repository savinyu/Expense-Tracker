<x-app-layout>

    {{-- Alpine scope wraps everything so the header button controls the modal --}}
    <div class="py-8" x-data="expenseModal()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-6">

            {{-- ── Page title + Add button ── --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('messages.dashboard_title') }}</h1>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format(__('messages.month_year_format')) }}</p>
                </div>
                <button
                    @click="open = true"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
                           text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('messages.add_expense') }}
                </button>
            </div>

            {{-- ── Pending Shared Expenses Inbox ── --}}
            @if(isset($pendingShared) && $pendingShared->isNotEmpty())
                <div x-data="{ open: false }"
                     class="rounded-2xl border border-amber-200 dark:border-amber-900
                            bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/30
                            shadow-sm overflow-hidden">

                    {{-- Alert header --}}
                    <div class="px-5 py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">
                                    {{ trans_choice('messages.pending_shared_alert', $pendingShared->count(), [
                                        'name'  => $user->roommate?->name ?? 'Your roommate',
                                        'count' => $pendingShared->count(),
                                    ]) }}
                                </p>
                            </div>
                        </div>
                        <button @click="open = !open"
                                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg
                                       bg-amber-600 hover:bg-amber-700 text-white transition-colors">
                            <span x-text="open ? '{{ __('messages.cancel') }}' : '{{ __('messages.review_pending') }}'"></span>
                            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Expandable list --}}
                    <div x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="border-t border-amber-200 dark:border-amber-900">
                        <ul class="divide-y divide-amber-100 dark:divide-amber-900/50">
                            @foreach($pendingShared as $pending)
                                <li class="flex items-center gap-4 px-5 py-3
                                           bg-white/40 dark:bg-gray-900/40 hover:bg-white/70 dark:hover:bg-gray-900/70 transition-colors">
                                    <x-category-icon :category="$pending->category"/>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $pending->description ?? '—' }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="inline-block px-2 py-px rounded-full text-xs font-medium
                                                         bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                                {{ $pending->category }}
                                            </span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                {{ $pending->expense_date->format('M d, Y') }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold whitespace-nowrap tabular-nums text-gray-900 dark:text-gray-100">
                                        {{ $pending->formattedAmount() }}
                                    </p>
                                    <form method="POST" action="{{ route('expenses.accept-shared', $pending) }}" class="shrink-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                                                       bg-green-600 hover:bg-green-700 text-white">
                                            {{ __('messages.accept_shared') }}
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- ── Flash Messages ── --}}
            @if(session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 5000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 max-h-20"
                    x-transition:leave-end="opacity-0 max-h-0"
                    class="flex items-center justify-between gap-3 rounded-xl px-5 py-3 text-sm overflow-hidden
                           bg-green-50 border border-green-200 text-green-800
                           dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-green-500 dark:text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="break-words min-w-0">{{ __(session('success')) }}</span>
                    </div>
                    <button @click="show = false" class="text-green-400 hover:text-green-600 dark:hover:text-green-200 transition shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 7000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 max-h-20"
                    x-transition:leave-end="opacity-0 max-h-0"
                    class="flex items-center justify-between gap-3 rounded-xl px-5 py-3 text-sm overflow-hidden
                           bg-red-50 border border-red-200 text-red-800
                           dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-red-500 dark:text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        <span class="break-words min-w-0">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-red-400 hover:text-red-600 dark:hover:text-red-200 transition shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- ── Summary Cards ── --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                {{-- Card 1 — Total Spent --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-5 flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                            {{ $hasFilters ? __('messages.total_filtered') : __('messages.total_spent_this_month') }}
                        </p>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1 tracking-tight">
                            {{ \App\Models\Expense::formatAmount($primaryTotal, $defaultCurrency) }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ $hasFilters ? __('messages.filter_active_label') : now()->format(__('messages.month_year_format')) }}
                        </p>
                    </div>
                </div>

                {{-- Card 2 — Top Category --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-5 flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-violet-50 dark:bg-violet-900/40 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                            {{ __('messages.top_category') }}
                        </p>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1 tracking-tight truncate">
                            {{ $topCategory ?? __('messages.top_category_none') }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format(__('messages.month_year_format')) }}</p>
                    </div>
                </div>

                {{-- Card 3 — Transaction Count --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-5 flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                     M9 5a2 2 0 002 2h2a2 2 0 002-2
                                     M9 5a2 2 0 012-2h2a2 2 0 012 2
                                     M9 12h6m-3-3v6"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                            {{ __('messages.transaction_count') }}
                        </p>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1 tracking-tight">
                            {{ $transactionCount }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ now()->format(__('messages.month_year_format')) }}</p>
                    </div>
                </div>

            </div>

            {{-- ── Spending by Category Chart ── --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6"
                 x-data="categoryChart()" x-init="init()">

                <div class="flex items-start justify-between gap-4 mb-1">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ __('messages.spending_by_category') }}</h2>
                    <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 shrink-0">
                        {{ $defaultCurrency }}
                    </span>
                </div>

                <p class="text-xs text-gray-400 dark:text-gray-500 mb-5">{{ now()->format(__('messages.month_year_format')) }}</p>

                <div id="chart-empty" class="hidden py-10 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('messages.no_expenses_this_month') }}</p>
                </div>

                <div id="chart-wrap" class="flex flex-col sm:flex-row items-center gap-8">
                    <div class="w-56 h-56 shrink-0">
                        <canvas id="categoryChart" x-ref="canvas"></canvas>
                    </div>
                    <ul id="chart-legend" class="flex flex-col gap-2 w-full"></ul>
                </div>
            </div>

            {{-- ── Filter Bar ── --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ __('messages.filter_expenses') }}</h2>
                    </div>
                    @if($hasFilters)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full
                                     bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                            {{ trans_choice('messages.active_filters', count($activeFilters), ['count' => count($activeFilters)]) }}
                        </span>
                    @endif
                </div>

                <form method="GET" action="{{ route('dashboard') }}" class="px-6 py-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">

                        {{-- View (Personal / Shared / All) — only useful when a roommate is linked --}}
                        @if($user->roommate_id)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                                {{ __('messages.filter_view') }}
                            </label>
                            <select name="view"
                                    class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400
                                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
                                <option value=""         {{ request('view') === ''         ? 'selected' : '' }}>{{ __('messages.filter_view_all') }}</option>
                                <option value="personal" {{ request('view') === 'personal' ? 'selected' : '' }}>{{ __('messages.filter_view_personal') }}</option>
                                <option value="shared"   {{ request('view') === 'shared'   ? 'selected' : '' }}>{{ __('messages.filter_view_shared') }}</option>
                            </select>
                        </div>
                        @endif

                        {{-- Category --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                                {{ __('messages.category') }}
                            </label>
                            <select name="category"
                                    class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400
                                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
                                <option value="">{{ __('messages.filter_all_categories') }}</option>
                                @foreach(['Food & Dining','Transport','Housing','Entertainment','Healthcare','Shopping','Utilities','Travel'] as $cat)
                                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Currency --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                                {{ __('messages.currency') }}
                            </label>
                            <select name="original_currency"
                                    class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400
                                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
                                <option value="">{{ __('messages.filter_all_currencies') }}</option>
                                @foreach(\App\Models\Expense::CURRENCIES as $cur)
                                    <option value="{{ $cur }}" {{ request('original_currency') === $cur ? 'selected' : '' }}>
                                        {{ $cur }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- From date --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                                {{ __('messages.filter_from') }}
                            </label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                   class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400
                                          bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
                        </div>

                        {{-- To date --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                                {{ __('messages.filter_to') }}
                            </label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                   class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400
                                          bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
                        </div>

                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <button type="submit"
                                class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white
                                       bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                            {{ __('messages.filter_apply') }}
                        </button>
                        <a href="{{ route('dashboard') }}"
                           class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                                  text-gray-600 dark:text-gray-300
                                  border border-gray-300 dark:border-gray-700
                                  hover:border-gray-400 dark:hover:border-gray-500
                                  {{ $hasFilters ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:border-red-300' : '' }}">
                            {{ __('messages.filter_clear') }}
                        </a>
                    </div>
                </form>
            </div>

            {{-- ── Expense List ── --}}
            {{-- $highSpendThreshold is in natural units (e.g. 100 = $100, 10000 = ¥10,000); --}}
            {{-- base_amount is in minor units (×100), so multiply threshold before comparing --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ __('messages.all_expenses') }}</h2>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-400 dark:text-gray-500">
                            {{ trans_choice('messages.entries', $expenses->count(), ['count' => $expenses->count()]) }}
                        </span>
                        @php $thresholdFormatted = \App\Models\Expense::formatAmount($highSpendThreshold * 100, $defaultCurrency); @endphp
                        <span class="inline-flex items-center gap-1 text-xs text-red-400 font-medium"
                              title="{{ __('messages.high_spend_note', ['amount' => $thresholdFormatted]) }}">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v4.5a.75.75 0 001.5 0v-4.5zm0 6.5a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('messages.high_spend_badge', ['amount' => $thresholdFormatted]) }}
                        </span>
                    </div>
                </div>

                @if($expenses->isEmpty())
                    <div class="py-16 text-center">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-gray-400 dark:text-gray-500 text-sm">{{ __('messages.no_expenses_yet') }}</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($expenses as $expense)
                            <li class="flex items-center gap-4 px-6 py-4
                                       hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                                <x-category-icon :category="$expense->category"/>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                        {{ $expense->description ?? '—' }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                        <span class="inline-block px-2 py-px rounded-full text-xs font-medium
                                                     bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400
                                                     whitespace-nowrap">
                                            {{ $expense->category }}
                                        </span>
                                        @if($expense->shared_status === \App\Models\Expense::SHARED_SHARED)
                                            <span class="inline-flex items-center gap-1 px-2 py-px rounded-full text-xs font-medium
                                                         bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300
                                                         whitespace-nowrap">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                {{ __('messages.shared_badge') }}
                                            </span>
                                        @elseif($expense->shared_status === \App\Models\Expense::SHARED_PENDING)
                                            <span class="inline-flex items-center gap-1 px-2 py-px rounded-full text-xs font-medium
                                                         bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300
                                                         whitespace-nowrap">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ __('messages.pending_badge') }}
                                            </span>
                                        @endif
                                        <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                            {{ $expense->expense_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold whitespace-nowrap tabular-nums
                                              {{ ($expense->base_amount ?? $expense->amount) >= $highSpendThreshold * 100
                                                  ? 'text-red-600 dark:text-red-400'
                                                  : 'text-gray-900 dark:text-gray-100' }}">
                                        {{ $expense->formattedAmount() }}
                                    </p>
                                    @if($expense->currency !== $defaultCurrency && $expense->base_amount)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 tabular-nums">
                                            {{ \App\Models\Expense::formatAmount($expense->base_amount, $defaultCurrency) }}
                                        </p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

            </div>

        </div>

        {{-- ════════════════════════════════════════
             Add Expense Modal
             ════════════════════════════════════════ --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog" aria-modal="true">

            {{-- Backdrop — independent x-show + direct variable assignment as failsafe --}}
            <div
                x-show="open"
                x-cloak
                @click="open = false"
                class="fixed inset-0 bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm"></div>

            {{-- Centering wrapper --}}
            <div class="flex min-h-full items-center justify-center p-4">

                {{-- Panel --}}
                <div
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-lg"
                    @click.stop>

                    {{-- Modal header --}}
                    <div class="flex items-center justify-between px-6 pt-6 pb-4
                                border-b border-gray-100 dark:border-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.add_new_expense') }}
                        </h2>
                        <button @click="close()"
                                class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300
                                       transition rounded-lg p-1 -mr-1"
                                aria-label="Close">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-5 space-y-5">

                        {{-- ── Receipt Scanner ── --}}
                        <div class="p-4 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900 rounded-xl">
                            <p class="text-sm font-medium text-indigo-800 dark:text-indigo-300 mb-3">
                                {{ __('messages.receipt_scan') }}
                            </p>

                            <div class="flex items-center gap-3 flex-wrap">

                                {{-- File picker --}}
                                <label class="cursor-pointer flex items-center gap-2 px-4 py-2 rounded-lg
                                              text-sm font-medium transition select-none
                                              bg-white dark:bg-gray-800
                                              border border-indigo-200 dark:border-indigo-800
                                              text-indigo-700 dark:text-indigo-300
                                              hover:bg-indigo-50 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="truncate max-w-[160px]" x-text="fileName || '{{ __('messages.choose_receipt') }}'"></span>
                                    <input type="file" accept="image/*" class="hidden" @change="onFileChange">
                                </label>

                                {{-- Scan button --}}
                                <button type="button" @click="scan()" :disabled="!file || scanning"
                                        class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition-colors
                                               bg-indigo-600 hover:bg-indigo-700 text-white
                                               disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg x-show="!scanning" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                                 M9 5a2 2 0 002 2h2a2 2 0 002-2
                                                 M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <svg x-show="scanning" class="w-4 h-4 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <span x-text="scanning ? '{{ __('messages.scanning') }}' : '{{ __('messages.scan_receipt') }}'"></span>
                                </button>

                            </div>

                            {{-- Progress bar --}}
                            <div x-show="scanProgress > 0"
                                 class="mt-3 h-1.5 bg-indigo-100 dark:bg-indigo-900/60 rounded-full overflow-hidden"
                                 style="display:none">
                                <div class="h-full bg-indigo-500 rounded-full transition-all ease-out duration-300"
                                     :style="`width: ${scanProgress}%`"></div>
                            </div>

                            {{-- Scanner success --}}
                            <div x-show="scanSuccess"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3 flex items-center justify-between gap-3 rounded-lg px-4 py-2.5 text-sm
                                        bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800
                                        text-green-800 dark:text-green-300"
                                 style="display:none">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500 dark:text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-text="scanSuccess"></span>
                                </div>
                                <button @click="scanSuccess = ''"
                                        class="text-green-400 hover:text-green-600 dark:hover:text-green-200 transition shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Scanner error --}}
                            <div x-show="scanError"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3 flex items-center justify-between gap-3 rounded-lg px-4 py-2.5 text-sm
                                        bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800
                                        text-red-800 dark:text-red-300"
                                 style="display:none">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-500 dark:text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                    <span x-text="scanError"></span>
                                </div>
                                <button @click="scanError = ''"
                                        class="text-red-400 hover:text-red-600 dark:hover:text-red-200 transition shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                        </div>

                        {{-- ── Expense form ── --}}
                        <form action="{{ route('dashboard.expenses.store') }}" method="POST"
                              x-data="{ currency: '{{ old('currency', $defaultCurrency) }}' }">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                {{-- Currency --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        {{ __('messages.currency') }}
                                    </label>
                                    <select name="currency" id="field-currency" x-model="currency"
                                            class="w-full px-3 py-2 border rounded-lg text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-400
                                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                                   {{ $errors->has('currency')
                                                       ? 'border-red-400 bg-red-50 dark:bg-red-900/20 dark:border-red-700'
                                                       : 'border-gray-300 dark:border-gray-700' }}">
                                        @foreach(\App\Models\Expense::CURRENCIES as $cur)
                                            <option value="{{ $cur }}"
                                                {{ old('currency', $defaultCurrency) === $cur ? 'selected' : '' }}>
                                                {{ $cur }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('currency')
                                        <p class="flex items-center gap-1 text-red-600 dark:text-red-400 text-xs mt-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Amount --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        {{ __('messages.amount') }}
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 dark:text-gray-500 text-sm font-medium"
                                              x-text="{ JPY:'¥', USD:'$', EUR:'€', GBP:'£', AUD:'A$', CAD:'C$', SGD:'S$' }[currency] ?? currency"></span>
                                        <input type="number" name="amount" id="field-amount" step="0.01" min="0.01"
                                               value="{{ old('amount') }}" placeholder="0.00"
                                               class="w-full pl-9 pr-3 py-2 border rounded-lg text-sm
                                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                                      bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                                      placeholder-gray-400 dark:placeholder-gray-600
                                                      {{ $errors->has('amount')
                                                          ? 'border-red-400 bg-red-50 dark:bg-red-900/20 dark:border-red-700'
                                                          : 'border-gray-300 dark:border-gray-700' }}">
                                    </div>
                                    @error('amount')
                                        <p class="flex items-center gap-1 text-red-600 dark:text-red-400 text-xs mt-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Category --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        {{ __('messages.category') }}
                                    </label>
                                    <select name="category" id="field-category"
                                            class="w-full px-3 py-2 border rounded-lg text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-400
                                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                                   {{ $errors->has('category')
                                                       ? 'border-red-400 bg-red-50 dark:bg-red-900/20 dark:border-red-700'
                                                       : 'border-gray-300 dark:border-gray-700' }}">
                                        <option value="">{{ __('messages.select_category') }}</option>
                                        @foreach(['Food & Dining', 'Transport', 'Housing', 'Entertainment', 'Healthcare', 'Shopping', 'Utilities', 'Travel'] as $cat)
                                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <p class="flex items-center gap-1 text-red-600 dark:text-red-400 text-xs mt-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Date --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        {{ __('messages.date') }}
                                    </label>
                                    <input type="date" name="expense_date" id="field-date"
                                           value="{{ old('expense_date', now()->format('Y-m-d')) }}"
                                           class="w-full px-3 py-2 border rounded-lg text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                                  {{ $errors->has('expense_date')
                                                      ? 'border-red-400 bg-red-50 dark:bg-red-900/20 dark:border-red-700'
                                                      : 'border-gray-300 dark:border-gray-700' }}">
                                    @error('expense_date')
                                        <p class="flex items-center gap-1 text-red-600 dark:text-red-400 text-xs mt-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                        {{ __('messages.description') }}
                                        <span class="text-gray-400 dark:text-gray-500 font-normal">{{ __('messages.description_hint') }}</span>
                                    </label>
                                    <input type="text" name="description" id="field-description"
                                           value="{{ old('description') }}"
                                           placeholder="{{ __('messages.description_placeholder') }}"
                                           class="w-full px-3 py-2 border rounded-lg text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                                  placeholder-gray-400 dark:placeholder-gray-600
                                                  {{ $errors->has('description')
                                                      ? 'border-red-400 bg-red-50 dark:bg-red-900/20 dark:border-red-700'
                                                      : 'border-gray-300 dark:border-gray-700' }}">
                                    @error('description')
                                        <p class="flex items-center gap-1 text-red-600 dark:text-red-400 text-xs mt-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                            </div>

                            {{-- Share with Roommate (only if a roommate is linked) --}}
                            @if($user->roommate_id)
                                <label class="mt-5 flex items-start gap-3 p-3 rounded-xl
                                              bg-indigo-50 dark:bg-indigo-950/40
                                              border border-indigo-100 dark:border-indigo-900 cursor-pointer
                                              hover:bg-indigo-100/60 dark:hover:bg-indigo-950/70 transition-colors">
                                    <input type="checkbox" name="share_with_roommate" value="1"
                                           {{ old('share_with_roommate') ? 'checked' : '' }}
                                           class="mt-0.5 w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600
                                                  focus:ring-2 focus:ring-indigo-400 cursor-pointer">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-indigo-800 dark:text-indigo-300">
                                            {{ __('messages.share_with_roommate') }}
                                        </p>
                                        <p class="text-xs text-indigo-600/80 dark:text-indigo-400/80 mt-0.5">
                                            {{ __('messages.share_with_roommate_hint') }}
                                        </p>
                                    </div>
                                </label>
                            @endif

                            {{-- Form actions --}}
                            <div class="flex items-center justify-end gap-3 mt-6 pt-5
                                        border-t border-gray-100 dark:border-gray-800">
                                <button type="button" @click="close()"
                                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                                               text-gray-600 dark:text-gray-300
                                               bg-white dark:bg-gray-800
                                               border border-gray-300 dark:border-gray-700
                                               hover:border-gray-400 dark:hover:border-gray-600">
                                    {{ __('messages.cancel') }}
                                </button>
                                <button type="submit"
                                        class="px-5 py-2 text-sm font-semibold text-white rounded-lg transition-colors
                                               bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800">
                                    {{ __('messages.add_expense') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end x-data --}}

    @push('scripts')
    <script>
    const CHART_COLORS = [
        '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b',
        '#10b981', '#3b82f6', '#ef4444', '#14b8a6',
    ];

    const CURRENCY_SYMBOLS  = { JPY:'¥', USD:'$', EUR:'€', GBP:'£', AUD:'A$', CAD:'C$', SGD:'S$' };
    const ZERO_DECIMAL_CURR = ['JPY'];

    function fmtAmount(value, currency) {
        const decimals = ZERO_DECIMAL_CURR.includes(currency) ? 0 : 2;
        const symbol   = CURRENCY_SYMBOLS[currency] ?? (currency + ' ');
        return symbol + value.toLocaleString(undefined, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    }

    const defaultCurrency = '{{ $defaultCurrency }}';

    function categoryChart() {
        return {
            init() {
                this.$nextTick(() => {
                    this.loadChart();
                });
            },

            loadChart() {
                // Forward active filter params so the chart reflects the same
                // filtered dataset as the expense list below it.
                const params = new URLSearchParams(window.location.search);
                fetch(`{{ route('dashboard.chart-data') }}?${params.toString()}`)
                    .then(res => res.json())
                    .then(data => {
                        const labels = Object.keys(data);
                        const values = Object.values(data).map(v => v / 100);
                        const total  = values.reduce((s, v) => s + v, 0);
                        const colors = labels.map((_, i) => CHART_COLORS[i % CHART_COLORS.length]);

                        const wrap  = document.getElementById('chart-wrap');
                        const empty = document.getElementById('chart-empty');

                        if (labels.length === 0) {
                            wrap.classList.add('hidden');
                            empty.classList.remove('hidden');
                            document.getElementById('chart-legend').innerHTML = '';
                            return;
                        }

                        wrap.classList.remove('hidden');
                        empty.classList.add('hidden');

                        const canvas = this.$refs.canvas;
                        let chart    = Chart.getChart(canvas);

                        if (chart) {
                            chart.data.labels                      = labels;
                            chart.data.datasets[0].data            = values;
                            chart.data.datasets[0].backgroundColor = colors;
                            chart.update();
                        } else {
                            const isDark  = document.documentElement.classList.contains('dark');
                            const borderC = isDark ? '#111827' : '#ffffff';

                            chart = new Chart(canvas, {
                                type: 'pie',
                                data: {
                                    labels,
                                    datasets: [{
                                        data:            values,
                                        backgroundColor: colors,
                                        borderWidth:     2,
                                        borderColor:     borderC,
                                        hoverOffset:     6,
                                    }],
                                },
                                options: {
                                    responsive:          true,
                                    maintainAspectRatio: true,
                                    animation:           { duration: 400 },
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            backgroundColor: isDark ? '#1f2937' : 'rgba(0,0,0,0.75)',
                                            titleColor:  isDark ? '#f3f4f6' : '#ffffff',
                                            bodyColor:   isDark ? '#d1d5db' : '#ffffff',
                                            callbacks: {
                                                label: function(ctx) {
                                                    const t   = ctx.chart.data.datasets[0].data.reduce((s, v) => s + v, 0);
                                                    const pct = ((ctx.parsed / t) * 100).toFixed(1);
                                                    return ' ' + fmtAmount(ctx.parsed, defaultCurrency) + ' (' + pct + '%)';
                                                },
                                            },
                                        },
                                    },
                                },
                            });
                        }

                        this.renderLegend(labels, values, colors, total);
                    })
                    .catch(err => console.error('Chart fetch error:', err));
            },

            renderLegend(labels, values, colors, total) {
                const legend = document.getElementById('chart-legend');
                legend.innerHTML = '';
                labels.forEach((label, i) => {
                    const pct = ((values[i] / total) * 100).toFixed(1);
                    legend.insertAdjacentHTML('beforeend', `
                        <li class="flex items-center justify-between gap-3 text-sm">
                            <span class="flex items-center gap-2 min-w-0">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background:${colors[i]}"></span>
                                <span class="text-gray-700 dark:text-gray-400 truncate">${label}</span>
                            </span>
                            <span class="text-gray-900 dark:text-gray-200 font-semibold whitespace-nowrap">
                                ${fmtAmount(values[i], defaultCurrency)}
                                <span class="text-gray-500 dark:text-gray-400 font-normal">(${pct}%)</span>
                            </span>
                        </li>
                    `);
                });
            },
        };
    }

    function expenseModal() {
        return {
            open: {{ $errors->any() ? 'true' : 'false' }},

            file:         null,
            fileName:     '',
            scanning:     false,
            scanProgress: 0,
            scanSuccess:  '',
            scanError:    '',

            close() { this.open = false; },

            onFileChange(e) {
                this.file         = e.target.files[0] ?? null;
                this.fileName     = this.file ? this.file.name : '';
                this.scanSuccess  = '';
                this.scanError    = '';
                this.scanProgress = 0;
            },

            async scan() {
                if (!this.file) return;

                this.scanning     = true;
                this.scanSuccess  = '';
                this.scanError    = '';
                this.scanProgress = 5;

                const timer = setInterval(() => {
                    if (this.scanProgress < 85) {
                        this.scanProgress += (85 - this.scanProgress) * 0.1;
                    }
                }, 300);

                const form = new FormData();
                form.append('receipt', this.file);
                form.append('_token', '{{ csrf_token() }}');

                try {
                    const res  = await fetch('{{ route('expenses.scan-receipt') }}', { method: 'POST', body: form });
                    const data = await res.json();

                    clearInterval(timer);
                    this.scanProgress = 100;

                    if (!res.ok || data.error) {
                        this.scanError = data.error ?? '{{ __('messages.scan_error_generic') }}';
                    } else {
                        const validCurrencies = @json(\App\Models\Expense::CURRENCIES);
                        const populated = [];
                        if (data.currency && validCurrencies.includes(data.currency)) {
                            const sel = document.getElementById('field-currency');
                            if (sel) {
                                sel.value = data.currency;
                                sel.dispatchEvent(new Event('change'));
                            }
                            populated.push('{{ __('messages.currency') }}');
                        }
                        if (data.category) {
                            const catSel = document.getElementById('field-category');
                            if (catSel) {
                                // Only set if the option actually exists in the dropdown
                                const match = Array.from(catSel.options).find(o => o.value === data.category);
                                if (match) {
                                    catSel.value = data.category;
                                    populated.push('{{ __('messages.category') }}');
                                }
                            }
                        }
                        if (data.total_amount != null) {
                            document.getElementById('field-amount').value = parseFloat(data.total_amount).toFixed(2);
                            populated.push('{{ __('messages.amount') }}');
                        }
                        if (data.date) {
                            document.getElementById('field-date').value = data.date;
                            populated.push('{{ __('messages.date') }}');
                        }
                        if (data.vendor_name) {
                            document.getElementById('field-description').value = data.vendor_name;
                            populated.push('{{ __('messages.description') }}');
                        }
                        this.scanSuccess = populated.length
                            ? '{{ __('messages.scan_success_prefix') }} ' + populated.join(', ') + '.'
                            : '{{ __('messages.scan_no_data') }}';
                    }
                } catch (err) {
                    clearInterval(timer);
                    this.scanProgress = 100;
                    this.scanError    = '{{ __('messages.scan_error_network') }}';
                } finally {
                    this.scanning = false;
                    setTimeout(() => { this.scanProgress = 0; }, 700);
                }
            },
        };
    }
    </script>
    @endpush

</x-app-layout>
