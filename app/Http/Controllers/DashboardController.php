<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\ExchangeRateService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __construct(private readonly ExchangeRateService $fx) {}

    public function index(Request $request): View
    {
        $user            = auth()->user();
        $defaultCurrency = $user->default_currency ?? 'JPY';

        // ── Determine whether any filter is active ──────────────────────────
        $activeFilters = array_filter(
            $request->only(['category', 'start_date', 'end_date', 'original_currency'])
        );
        $hasFilters = !empty($activeFilters);

        // ── Base query factory — always returns a fresh Eloquent Builder ─────
        // Using Expense::where() instead of $user->expenses() avoids the
        // HasMany → Builder type mismatch when passed to applyFilters().
        $base = fn () => Expense::where('user_id', $user->id);

        // ── Filtered expense list (used for the table) ──────────────────────
        $expenses = $this->applyFilters($base()->latest('expense_date'), $request)->get();

        // ── Summary KPIs ─────────────────────────────────────────────────────
        // When filters are active: stats reflect the filtered set.
        // When no filters:         stats always show the current calendar month.
        if ($hasFilters) {
            $statsQuery = fn () => $this->applyFilters($base(), $request);
        } else {
            $statsQuery = fn () => $base()
                ->whereYear('expense_date', now()->year)
                ->whereMonth('expense_date', now()->month);
        }

        $primaryTotal     = (int) $statsQuery()->sum('base_amount');
        $transactionCount = $statsQuery()->count();
        $topCategoryRow   = $statsQuery()
            ->selectRaw('category, SUM(base_amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();
        $topCategory = $topCategoryRow?->category;

        // ── High-spend threshold ─────────────────────────────────────────────
        $defaultThreshold   = in_array($defaultCurrency, ['JPY']) ? 10000 : 100;
        $highSpendThreshold = $user->high_spend_threshold ?? $defaultThreshold;

        return view('dashboard', compact(
            'expenses',
            'primaryTotal',
            'transactionCount',
            'topCategory',
            'defaultCurrency',
            'highSpendThreshold',
            'activeFilters',
            'hasFilters',
        ));
    }

    public function categoryBreakdown(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $query = Expense::where('user_id', $user->id);

        // Default to current month when no date filter is supplied.
        $hasDateFilter = $request->filled('start_date') || $request->filled('end_date');
        if (!$hasDateFilter) {
            $query->whereYear('expense_date', now()->year)
                  ->whereMonth('expense_date', now()->month);
        }

        $this->applyFilters($query, $request);

        $breakdown = $query
            ->groupBy('category')
            ->selectRaw('category, SUM(base_amount) as total')
            ->pluck('total', 'category');

        return response()->json($breakdown);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'currency'     => ['required', 'string', Rule::in(Expense::CURRENCIES)],
            'category'     => ['required', 'string', 'max:100'],
            'expense_date' => ['required', 'date'],
            'description'  => ['nullable', 'string', 'max:255'],
        ]);

        $user            = auth()->user();
        $defaultCurrency = $user->default_currency ?? 'JPY';

        $originalAmount   = (int) round($validated['amount'] * 100);
        $originalCurrency = $validated['currency'];

        ['base_amount' => $baseAmount, 'exchange_rate' => $rate] =
            $this->fx->convert($originalAmount, $originalCurrency, $defaultCurrency);

        $user->expenses()->create([
            'amount'            => $originalAmount,
            'currency'          => $originalCurrency,
            'original_amount'   => $originalAmount,
            'original_currency' => $originalCurrency,
            'base_amount'       => $baseAmount,
            'exchange_rate'     => $rate,
            'category'          => $validated['category'],
            'expense_date'      => $validated['expense_date'],
            'description'       => $validated['description'] ?? null,
        ]);

        return redirect()->route('dashboard')->with('success', 'messages.expense_added');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Apply the four dashboard filters to an existing query builder.
     * Uses `when()` so missing / empty params are silently skipped.
     */
    private function applyFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when(
                $request->filled('category'),
                fn ($q) => $q->where('category', $request->input('category'))
            )
            ->when(
                $request->filled('start_date'),
                fn ($q) => $q->whereDate('expense_date', '>=', $request->input('start_date'))
            )
            ->when(
                $request->filled('end_date'),
                fn ($q) => $q->whereDate('expense_date', '<=', $request->input('end_date'))
            )
            ->when(
                $request->filled('original_currency'),
                fn ($q) => $q->where('original_currency', $request->input('original_currency'))
            );
    }
}
