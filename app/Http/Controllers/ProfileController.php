<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Expense;
use App\Models\User;
use App\Services\ExchangeRateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly ExchangeRateService $fx) {}

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('roommate'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user      = $request->user();
        $validated = $request->validated();

        // ── Detect default-currency change BEFORE filling ────────────────────
        // Compare the incoming value against what's currently on the model so
        // we know whether to trigger a rebase pass over the user's expenses.
        $oldCurrency      = $user->default_currency;
        $newCurrency      = $validated['default_currency'] ?? $oldCurrency;
        $currencyChanged  = $newCurrency !== $oldCurrency;

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // ── Rebase all expenses if the default currency changed ──────────────
        if ($currencyChanged) {
            $rebased = $this->rebaseUserExpenses($user, $newCurrency);
            Log::info("Rebased {$rebased} expenses for user {$user->id}: {$oldCurrency} → {$newCurrency}");
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Recalculate base_amount + exchange_rate for every expense the user owns,
     * converting from each expense's own original_currency into $newCurrency.
     *
     * Uses ExchangeRateService (cached rates) and runs inside a transaction so
     * a partial failure can't leave half the expenses on the new currency and
     * half on the old.
     *
     * @return int Number of expenses updated.
     */
    private function rebaseUserExpenses(User $user, string $newCurrency): int
    {
        $updated = 0;

        DB::transaction(function () use ($user, $newCurrency, &$updated) {
            // chunkById is memory-safe even if the user has thousands of rows.
            $user->expenses()->chunkById(200, function ($expenses) use ($newCurrency, &$updated) {
                foreach ($expenses as $expense) {
                    // Defensive: skip rows that are missing the original-currency
                    // data we'd need to do the conversion safely.
                    if (!$expense->original_amount || !$expense->original_currency) {
                        continue;
                    }

                    ['base_amount' => $newBase, 'exchange_rate' => $newRate] =
                        $this->fx->convert(
                            $expense->original_amount,
                            $expense->original_currency,
                            $newCurrency,
                        );

                    $expense->update([
                        'base_amount'   => $newBase,
                        'exchange_rate' => $newRate,
                    ]);

                    $updated++;
                }
            });
        });

        return $updated;
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    // ── Roommate linking ──────────────────────────────────────────────────────

    /**
     * Link the current user to another user by email — mutually.
     */
    public function linkRoommate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'roommate_email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $user = $request->user();

        if ($user->roommate_id) {
            return back()->withErrors([
                'roommate_email' => __('messages.roommate_already_linked'),
            ]);
        }

        $other = User::where('email', $validated['roommate_email'])->first();

        if (!$other) {
            return back()->withErrors([
                'roommate_email' => __('messages.roommate_not_found'),
            ]);
        }

        if ($other->id === $user->id) {
            return back()->withErrors([
                'roommate_email' => __('messages.roommate_self'),
            ]);
        }

        if ($other->roommate_id) {
            return back()->withErrors([
                'roommate_email' => __('messages.roommate_other_taken'),
            ]);
        }

        // Both updates in a transaction — never leave one side dangling.
        DB::transaction(function () use ($user, $other) {
            $user->update(['roommate_id'  => $other->id]);
            $other->update(['roommate_id' => $user->id]);
        });

        return Redirect::route('profile.edit')->with('success', 'messages.roommate_linked_success');
    }

    /**
     * Unlink the current user from their roommate — mutually.
     * Resets any shared/pending expenses between them back to personal.
     */
    public function unlinkRoommate(Request $request): RedirectResponse
    {
        $user = $request->user();
        $other = $user->roommate;

        if (!$other) {
            return Redirect::route('profile.edit');
        }

        DB::transaction(function () use ($user, $other) {
            // Mutual unlink
            $user->update(['roommate_id'  => null]);
            $other->update(['roommate_id' => null]);

            // Reset shared/pending expenses on both sides back to personal
            Expense::whereIn('user_id', [$user->id, $other->id])
                ->whereIn('shared_status', [Expense::SHARED_PENDING, Expense::SHARED_SHARED])
                ->update(['shared_status' => Expense::SHARED_PERSONAL]);
        });

        return Redirect::route('profile.edit')->with('success', 'messages.roommate_unlinked_success');
    }
}
