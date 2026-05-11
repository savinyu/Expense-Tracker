<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('roommate'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
