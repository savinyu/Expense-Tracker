<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\User;
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
        $user            = auth()->user()->load('roommate');
        $defaultCurrency = $user->default_currency ?? 'JPY';
        $roommateId      = $user->roommate_id;

        // ── Active filter detection ─────────────────────────────────────────
        $activeFilters = array_filter(
            $request->only(['category', 'start_date', 'end_date', 'original_currency', 'view'])
        );
        $hasFilters = !empty($activeFilters);

        // ── Filtered expense list ────────────────────────────────────────────
        $expenses = $this->applyFilters(
            $this->buildBaseQuery($user)->latest('expense_date'),
            $request
        )->get();

        // ── Summary KPIs ─────────────────────────────────────────────────────
        $statsQuery = $hasFilters
            ? fn () => $this->applyFilters($this->buildBaseQuery($user), $request)
            : fn () => $this->buildBaseQuery($user)
                ->whereYear('expense_date', now()->year)
                ->whereMonth('expense_date', now()->month);

        $primaryTotal     = (int) $statsQuery()->sum('base_amount');
        $transactionCount = $statsQuery()->count();
        $topCategoryRow   = $statsQuery()
            ->selectRaw('category, SUM(base_amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();
        $topCategory = $topCategoryRow?->category;

        // ── High-spend threshold ─────────────────────────────────────────────
        $highSpendThreshold = $this->resolveHighSpendThreshold($user);

        // ── Pending inbox ────────────────────────────────────────────────────
        // Expenses my roommate created with shared_status='pending', awaiting my review.
        $pendingShared = collect();
        if ($roommateId) {
            $pendingShared = Expense::where('user_id', $roommateId)
                ->where('shared_status', Expense::SHARED_PENDING)
                ->latest('expense_date')
                ->get();
        }

        return view('dashboard', compact(
            'expenses',
            'primaryTotal',
            'transactionCount',
            'topCategory',
            'defaultCurrency',
            'highSpendThreshold',
            'activeFilters',
            'hasFilters',
            'user',
            'pendingShared',
        ));
    }

    /**
     * Returns BOTH the chart breakdown AND the rendered expense-list HTML so
     * the dashboard's month selector can swap the chart and the list in a
     * single fetch round-trip.
     *
     * Response shape:
     *   {
     *     "chartData": { "Food & Dining": 4250, "Transport": 1200, ... },
     *     "html":      "<div class=\"px-6 py-4 ...\">...</div>"
     *   }
     */
    public function categoryBreakdown(Request $request): JsonResponse
    {
        $user            = auth()->user();
        $defaultCurrency = $user->default_currency ?? 'JPY';

        // Two queries off the shared visibility scope — one aggregated for the
        // chart, one as a row list (ordered by date) for the partial.
        $chartQuery = $this->buildBaseQuery($user);
        $listQuery  = $this->buildBaseQuery($user)->latest('expense_date');

        // Same date-scope and filter logic for both queries — keeps the chart
        // and the list visually consistent (same month, same filters).
        $this->applyChartDateScope($chartQuery, $request);
        $this->applyChartDateScope($listQuery, $request);

        $this->applyFilters($chartQuery, $request);
        $this->applyFilters($listQuery,  $request);

        // Aggregated chart data
        $chartData = $chartQuery
            ->groupBy('category')
            ->selectRaw('category, SUM(base_amount) as total')
            ->pluck('total', 'category');

        // Filtered expense rows + rendered partial
        $expenses           = $listQuery->get();
        $highSpendThreshold = $this->resolveHighSpendThreshold($user);

        $html = view('dashboard.partials.expense-list', compact(
            'expenses', 'highSpendThreshold', 'defaultCurrency'
        ))->render();

        // Sum the normalized base_amount across the same filtered set and format
        // it with the user's default-currency symbol (e.g. "¥123,400" or "$87.50").
        // Sum from the in-memory Collection so the math agrees byte-for-byte with
        // the rows the user can see in the list.
        $totalBaseMinor = $expenses->sum('base_amount');
        $totalSpent     = Expense::formatAmount((int) $totalBaseMinor, $defaultCurrency);

        return response()->json([
            'chartData'  => $chartData,
            'html'       => $html,
            'totalSpent' => $totalSpent,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'currency'            => ['required', 'string', Rule::in(Expense::CURRENCIES)],
            'category'            => ['required', 'string', 'max:100'],
            'expense_date'        => ['required', 'date'],
            'description'         => ['nullable', 'string', 'max:255'],
            'share_with_roommate' => ['nullable', 'boolean'],
        ]);

        $user            = auth()->user();
        $defaultCurrency = $user->default_currency ?? 'JPY';

        $originalAmount   = (int) round($validated['amount'] * 100);
        $originalCurrency = $validated['currency'];

        ['base_amount' => $baseAmount, 'exchange_rate' => $rate] =
            $this->fx->convert($originalAmount, $originalCurrency, $defaultCurrency);

        // Mark as 'pending' if the user opted to share AND they actually have a roommate.
        // Without a roommate, the checkbox should never appear — but defend in depth.
        $sharedStatus = ($request->boolean('share_with_roommate') && $user->roommate_id)
            ? Expense::SHARED_PENDING
            : Expense::SHARED_PERSONAL;

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
            'shared_status'     => $sharedStatus,
        ]);

        return redirect()->route('dashboard')->with('success', 'messages.expense_added');
    }

    /**
     * Roommate accepts a pending shared expense.
     */
    public function acceptShared(Request $request, Expense $expense): RedirectResponse
    {
        $user = auth()->user();

        // Only the linked roommate of the creator can accept, and the expense
        // must still be in 'pending' state.
        if ($expense->user_id !== $user->roommate_id ||
            $expense->shared_status !== Expense::SHARED_PENDING) {
            abort(403);
        }

        $expense->update(['shared_status' => Expense::SHARED_SHARED]);

        return back()->with('success', 'messages.shared_accepted');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Build the base visibility scope for a user:
     *   - Their own expenses (any shared_status), OR
     *   - Their linked roommate's expenses that have been accepted as 'shared'.
     *
     * Used by index() AND categoryBreakdown() so the chart and the list always
     * see exactly the same row set.
     */
    private function buildBaseQuery(User $user): Builder
    {
        $roommateId = $user->roommate_id;

        return Expense::query()->where(function (Builder $q) use ($user, $roommateId) {
            $q->where('user_id', $user->id);

            if ($roommateId) {
                $q->orWhere(function (Builder $qq) use ($roommateId) {
                    $qq->where('user_id', $roommateId)
                       ->where('shared_status', Expense::SHARED_SHARED);
                });
            }
        });
    }

    /**
     * Apply the chart-specific date scope to a query.
     * Priority: explicit month+year → start/end date → current month default.
     */
    private function applyChartDateScope(Builder $query, Request $request): void
    {
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('expense_date', $request->integer('month'))
                  ->whereYear('expense_date',  $request->integer('year'));
        } elseif (!$request->filled('start_date') && !$request->filled('end_date')) {
            $query->whereYear('expense_date',  now()->year)
                  ->whereMonth('expense_date', now()->month);
        }
    }

    /**
     * Resolve the high-spend threshold for the user:
     * their custom value, or a currency-aware default.
     * Returned in natural units (e.g. 100 = $100, 10000 = ¥10,000).
     */
    private function resolveHighSpendThreshold(User $user): int
    {
        $defaultCurrency  = $user->default_currency ?? 'JPY';
        $defaultThreshold = in_array($defaultCurrency, ['JPY']) ? 10000 : 100;

        return $user->high_spend_threshold ?? $defaultThreshold;
    }

    /**
     * Apply the five dashboard filters to an existing query builder.
     *   - category, start_date, end_date, original_currency, view (all/personal/shared)
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
            )
            ->when(
                $request->input('view') === 'personal',
                fn ($q) => $q->where('shared_status', Expense::SHARED_PERSONAL)
            )
            ->when(
                $request->input('view') === 'shared',
                fn ($q) => $q->where('shared_status', Expense::SHARED_SHARED)
            );
    }
}
