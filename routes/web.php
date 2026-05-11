<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptScanController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Locale switcher — no auth required so login page can also be translated
Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'ja'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('locale.switch');

// Redirect root to dashboard (or login if unauthenticated)
Route::get('/', fn () => redirect()->route('dashboard'));

// ── Demo login (hackathon judges) ───────────────────────────────────────────
// Bypasses the normal credentials flow by logging in the first seeded user.
Route::get('/demo-login', function () {
    $user = User::first();

    if (!$user) {
        return redirect()->route('login')
            ->with('error', 'No demo user available. Run `php artisan db:seed` first.');
    }

    Auth::login($user);
    session()->regenerate();

    return redirect()->route('dashboard');
})->name('demo-login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'categoryBreakdown'])->name('dashboard.chart-data');
    Route::post('/expenses', [DashboardController::class, 'store'])->name('dashboard.expenses.store');
    Route::get('/expenses/export', [ExpenseController::class, 'exportCsv'])->name('expenses.export');
    Route::post('/expenses/scan-receipt', [ReceiptScanController::class, 'scan'])->name('expenses.scan-receipt');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Roommate linking (1-to-1 mutual)
    Route::post('/profile/roommate', [ProfileController::class, 'linkRoommate'])->name('profile.roommate.link');
    Route::delete('/profile/roommate', [ProfileController::class, 'unlinkRoommate'])->name('profile.roommate.unlink');

    // Accept a pending shared expense from your roommate
    Route::patch('/expenses/{expense}/accept-shared', [DashboardController::class, 'acceptShared'])->name('expenses.accept-shared');
});

require __DIR__.'/auth.php';
