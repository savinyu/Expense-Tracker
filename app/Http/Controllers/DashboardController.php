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
        $user            = auth()->user()->load('roommate');
        $defaultCurrency = $user->default_currency ?? 'JPY';
        $roommateId      = $user->roommate_id;

        // ── Active filter detection ─────────────────────────────────────────
        $activeFilters = array_filter(
            $request->only(['category', 'start_date', 'end_date', 'original_currency', 'view'])
        );
        $hasFilters = !empty($activeFilters);

        // ── Visibility scope ─────────────────────────────────────────────────
        // A user can see:
        //   1. All of their own expenses (any shared_status), AND
        //   2. Their roommate's expenses marked as 'shared' (accepted)
        $base = fn () => Expense::query()->where(function (Builder $q) use ($user, $roommateId) {
            $q->where('user_id', $user->id);

            if ($roommateId) {
                $q->orWhere(function (Builder $qq) use ($roommateId) {
                    $qq->where('user_id', $roommateId)
                       ->where('shared_status', Expense::SHARED_SHARED);
                });
            }
        });

        // ── Filtered expense list ────────────────────────────────────────────
        $expenses = $this->applyFilters($base()->latest('expense_date'), $request)->get();

        // ── Summary KPIs ─────────────────────────────────────────────────────
        $statsQuery = $hasFilters
            ? fn () => $this->applyFilters($base(), $request)
            : fn () => $base()
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
        $defaultThreshold   = in_array($defaultCurrency, ['JPY']) ? 10000 : 100;
        $highSpendThreshold = $user->high_spend_threshold ?? $defaultThreshold;

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

    public function categoryBreakdown(Request $request): JsonResponse
    {
        $user       = auth()->user();
        $roommateId = $user->roommate_id;

        // Same visibility scope as the dashboard table — keeps the chart in sync.
        $query = Expense::query()->where(function (Builder $q) use ($user, $roommateId) {
            $q->where('user_id', $user->id);
            if ($roommateId) {
                $q->orWhere(function (Builder $qq) use ($roommateId) {
                    $qq->where('user_id', $roommateId)
                       ->where('shared_status', Expense::SHARED_SHARED);
                });
            }
        });

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
