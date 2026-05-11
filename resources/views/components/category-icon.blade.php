@props(['category'])

@php
$key = match(true) {
    in_array($category, ['Meals', 'Food & Dining']) => 'food',
    in_array($category, ['Transit', 'Transport'])   => 'transit',
    $category === 'Subscriptions'                   => 'subscriptions',
    $category === 'Shopping'                        => 'shopping',
    $category === 'Healthcare'                      => 'healthcare',
    $category === 'Entertainment'                   => 'entertainment',
    $category === 'Housing'                         => 'housing',
    $category === 'Utilities'                       => 'utilities',
    $category === 'Travel'                          => 'travel',
    default                                         => 'default',
};

$ring = match($key) {
    'food'          => ['bg-orange-100 dark:bg-orange-900/30', 'text-orange-500 dark:text-orange-400'],
    'transit'       => ['bg-blue-100   dark:bg-blue-900/30',   'text-blue-500   dark:text-blue-400'],
    'subscriptions' => ['bg-violet-100 dark:bg-violet-900/30', 'text-violet-500 dark:text-violet-400'],
    'shopping'      => ['bg-pink-100   dark:bg-pink-900/30',   'text-pink-500   dark:text-pink-400'],
    'healthcare'    => ['bg-red-100    dark:bg-red-900/30',    'text-red-500    dark:text-red-400'],
    'entertainment' => ['bg-teal-100   dark:bg-teal-900/30',   'text-teal-500   dark:text-teal-400'],
    'housing'       => ['bg-yellow-100 dark:bg-yellow-900/30', 'text-yellow-500 dark:text-yellow-400'],
    'utilities'     => ['bg-amber-100  dark:bg-amber-900/30',  'text-amber-500  dark:text-amber-400'],
    'travel'        => ['bg-sky-100    dark:bg-sky-900/30',    'text-sky-500    dark:text-sky-400'],
    default         => ['bg-gray-100   dark:bg-gray-800',      'text-gray-400   dark:text-gray-500'],
};
@endphp

<div class="w-10 h-10 rounded-full {{ $ring[0] }} flex items-center justify-center shrink-0">
    <svg class="w-5 h-5 {{ $ring[1] }}" fill="none" stroke="currentColor" stroke-width="1.75"
         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
        @switch($key)

            @case('food')
                {{-- Coffee cup --}}
                <path d="M17 8h1a4 4 0 010 8h-1"/>
                <path d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"/>
                <line x1="6" y1="2" x2="6" y2="5"/>
                <line x1="10" y1="2" x2="10" y2="5"/>
                <line x1="14" y1="2" x2="14" y2="5"/>
                @break

            @case('transit')
                {{-- Bus --}}
                <path d="M5 17H3a2 2 0 01-2-2V9l2-5h16l2 5v6a2 2 0 01-2 2h-2"/>
                <circle cx="7" cy="17" r="2"/>
                <circle cx="17" cy="17" r="2"/>
                <path d="M5 9h14"/>
                @break

            @case('subscriptions')
                {{-- Refresh / repeat --}}
                <polyline points="1 4 1 10 7 10"/>
                <polyline points="23 20 23 14 17 14"/>
                <path d="M20.49 9A9 9 0 005.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 013.51 15"/>
                @break

            @case('shopping')
                {{-- Shopping bag --}}
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
                @break

            @case('healthcare')
                {{-- Heart --}}
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                @break

            @case('entertainment')
                {{-- Film strip --}}
                <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/>
                <line x1="7" y1="2" x2="7" y2="22"/>
                <line x1="17" y1="2" x2="17" y2="22"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <line x1="2" y1="7" x2="7" y2="7"/>
                <line x1="2" y1="17" x2="7" y2="17"/>
                <line x1="17" y1="17" x2="22" y2="17"/>
                <line x1="17" y1="7" x2="22" y2="7"/>
                @break

            @case('housing')
                {{-- Home --}}
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
                @break

            @case('utilities')
                {{-- Lightning bolt --}}
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                @break

            @case('travel')
                {{-- Airplane --}}
                <path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 00-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                @break

            @default
                {{-- Tag --}}
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
                @break

        @endswitch
    </svg>
</div>
