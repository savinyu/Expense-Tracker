{{--
    Expense list partial.

    Renders the card-internal content for the expense list: the header row
    (title + count + high-spend badge) and either the empty state or the <ul>.

    Required variables:
        $expenses           — Collection of Expense models (already filtered/ordered)
        $highSpendThreshold — int, natural units (e.g. 100 = $100, 10000 = ¥10,000)
        $defaultCurrency    — string, e.g. 'JPY', for the badge label and conversions

    Rendered statically by DashboardController@index AND dynamically (via
    ->render()) by DashboardController@categoryBreakdown, so both code paths
    must keep these three variables in scope.
--}}
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
