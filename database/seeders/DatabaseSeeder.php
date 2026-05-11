<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // ── Expense distribution ───────────────────────────────────────────────────
    // Category names must match ExpenseFactory::CATALOGUE keys exactly.
    // Columns: category, prev-month count, current-month count.
    // Total: 27 previous + 26 current = 53 base rows, plus 6 currency-pinned rows = 59.
    private const DISTRIBUTION = [
        ['Food & Dining',   10,  8],
        ['Transport',        6,  5],
        ['Shopping',         4,  4],
        ['Utilities',        4,  4],
        ['Healthcare',       2,  3],
        ['Entertainment',    1,  2],
    ];

    public function run(): void
    {
        // ── Test user ────────────────────────────────────────────────────────
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'             => 'Test User',
                'password'         => Hash::make('password'),
                'default_currency' => 'JPY',
            ]
        );

        // Wipe existing expenses so re-seeding is always clean
        $testUser->expenses()->delete();

        // ── Base distribution (random currencies, JPY-weighted 50/30/20) ────
        foreach (self::DISTRIBUTION as [$category, $prevCount, $currCount]) {
            Expense::factory($prevCount)
                ->forCategory($category)
                ->previousMonth()
                ->create(['user_id' => $testUser->id]);

            Expense::factory($currCount)
                ->forCategory($category)
                ->currentMonth()
                ->create(['user_id' => $testUser->id]);
        }

        // ── Pinned-currency rows (always visible in the list for demo) ───────
        // 3 × USD shopping expenses this month
        Expense::factory(3)
            ->forCategory('Shopping')
            ->currentMonth()
            ->withCurrency('USD')
            ->create(['user_id' => $testUser->id]);

        // 2 × USD food expenses this month (e.g. imported groceries, DoorDash)
        Expense::factory(2)
            ->forCategory('Food & Dining')
            ->currentMonth()
            ->withCurrency('USD')
            ->create(['user_id' => $testUser->id]);

        // 3 × INR expenses this month (e.g. Indian OTT, tech subscriptions)
        Expense::factory(3)
            ->forCategory('Utilities')
            ->currentMonth()
            ->withCurrency('INR')
            ->create(['user_id' => $testUser->id]);

        // 2 × INR travel expenses (e.g. IndiGo flight, hotel booking)
        Expense::factory(2)
            ->forCategory('Travel')
            ->currentMonth()
            ->withCurrency('INR')
            ->create(['user_id' => $testUser->id]);

        // ── Background users (multi-tenant isolation testing) ────────────────
        User::factory(5)
            ->has(Expense::factory()->count(10))
            ->create();

        $this->command->info('✓ Test user seeded with ' . $testUser->expenses()->count() . ' expenses.');
    }
}
