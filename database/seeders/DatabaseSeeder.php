<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Demo exchange rates — JPY is the base currency for both users.
     * Mirrors database/factories/ExpenseFactory.php so seeding is fully offline.
     *   1 USD = 155 JPY     |     1 INR = 1.8 JPY     |     1 VND = 0.0061 JPY
     */
    private const RATES = [
        'JPY' => 1.0,
        'USD' => 155.0,
        'INR' => 1.8,
        'VND' => 0.0061,
    ];

    public function run(): void
    {
        // ── Roommates ────────────────────────────────────────────────────────
        // Both default to JPY (they live in Tokyo) and share a ¥10,000 high-spend
        // alert threshold so red-highlighted rows in the demo are believable.
        $vaibhav = User::firstOrCreate(
            ['email' => 'vaibhav@example.com'],
            [
                'name'                 => 'Vaibhav',
                'password'             => Hash::make('password'),
                'default_currency'     => 'JPY',
                'high_spend_threshold' => 10000,
            ]
        );

        $savinyu = User::firstOrCreate(
            ['email' => 'savinyu@example.com'],
            [
                'name'                 => 'Savinyu',
                'password'             => Hash::make('password'),
                'default_currency'     => 'JPY',
                'high_spend_threshold' => 10000,
            ]
        );

        // Mutual roommate link (1-to-1).
        $vaibhav->forceFill(['roommate_id' => $savinyu->id])->save();
        $savinyu->forceFill(['roommate_id' => $vaibhav->id])->save();

        // Wipe their existing expenses so re-seeding stays idempotent.
        $vaibhav->expenses()->delete();
        $savinyu->expenses()->delete();

        // ── Vaibhav's ledger ─────────────────────────────────────────────────
        $this->seedVaibhav($vaibhav);

        // ── Savinyu's ledger ─────────────────────────────────────────────────
        $this->seedSavinyu($savinyu);

        $this->command->info('✓ Seeded ' . User::count() . ' users (Vaibhav + Savinyu) with '
            . Expense::count() . ' expenses across JPY / USD / VND / INR.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Per-user seeders
    // ─────────────────────────────────────────────────────────────────────────

    private function seedVaibhav(User $user): void
    {
        $today    = Carbon::today();
        $thisMon  = $today->copy()->startOfMonth();
        $lastMon  = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $vietnam  = $today->copy()->startOfMonth()->subDays(16);   // ~late prev-month

        // ── Tokyo personal — JPY (current month) ────────────────────────────
        $this->expense($user, 'Food & Dining', 'Sukiya',                'JPY',     580, $thisMon->copy()->addDays(1));
        $this->expense($user, 'Food & Dining', 'Lawson Café',           'JPY',     720, $thisMon->copy()->addDays(2));
        $this->expense($user, 'Food & Dining', 'Starbucks Shibuya',     'JPY',     580, $thisMon->copy()->addDays(3));
        $this->expense($user, 'Food & Dining', "McDonald's",            'JPY',     850, $thisMon->copy()->addDays(5));
        $this->expense($user, 'Food & Dining', 'Saizeriya',             'JPY',   1_250, $thisMon->copy()->addDays(7));
        $this->expense($user, 'Food & Dining', 'Yoshinoya',             'JPY',     690, $thisMon->copy()->addDays(8));
        $this->expense($user, 'Food & Dining', 'CoCo Ichibanya',        'JPY',   1_180, $thisMon->copy()->addDays(10));
        $this->expense($user, 'Food & Dining', 'Hotto Motto',           'JPY',     720, $thisMon->copy()->addDays(11));
        $this->expense($user, 'Transport',     'Suica top-up',          'JPY',   5_000, $thisMon->copy()->addDays(1));
        $this->expense($user, 'Transport',     'Taxi (rainy night)',    'JPY',   2_200, $thisMon->copy()->addDays(6));
        $this->expense($user, 'Shopping',      'Don Quijote',           'JPY',   3_400, $thisMon->copy()->addDays(4));
        $this->expense($user, 'Shopping',      'UNIQLO summer tee',     'JPY',   2_490, $thisMon->copy()->addDays(9));
        $this->expense($user, 'Entertainment', 'Toho Cinema',           'JPY',   1_800, $thisMon->copy()->addDays(7));
        $this->expense($user, 'Healthcare',    'Matsumoto Kiyoshi',     'JPY',   1_240, $thisMon->copy()->addDays(8));

        // ── Tokyo personal — JPY (previous month) ───────────────────────────
        $this->expense($user, 'Food & Dining', 'FamilyMart',            'JPY',     920, $lastMon->copy()->addDays(3));
        $this->expense($user, 'Food & Dining', 'Ootoya',                'JPY',   1_580, $lastMon->copy()->addDays(8));
        $this->expense($user, 'Food & Dining', '7-Eleven breakfast',    'JPY',     480, $lastMon->copy()->addDays(12));
        $this->expense($user, 'Transport',     'Suica top-up',          'JPY',   5_000, $lastMon->copy()->addDays(1));
        $this->expense($user, 'Transport',     'Shinkansen to Osaka',   'JPY',   8_400, $lastMon->copy()->addDays(15));
        $this->expense($user, 'Shopping',      'Amazon Japan',          'JPY',   4_500, $lastMon->copy()->addDays(10));
        $this->expense($user, 'Shopping',      'Daiso essentials',      'JPY',     880, $lastMon->copy()->addDays(18));
        $this->expense($user, 'Entertainment', 'Big Echo Karaoke',      'JPY',   2_400, $lastMon->copy()->addDays(20));
        $this->expense($user, 'Healthcare',    'Dental Checkup',        'JPY',   4_500, $lastMon->copy()->addDays(14));

        // ── Shared household bills — Vaibhav paid (visible to both) ─────────
        $this->expense($user, 'Housing',   'Tokyo apartment rent',  'JPY', 120_000, $lastMon->copy()->addDays(1),  'shared');
        $this->expense($user, 'Housing',   'Tokyo apartment rent',  'JPY', 120_000, $thisMon->copy()->addDays(1),  'shared');
        $this->expense($user, 'Utilities', 'TEPCO electric bill',   'JPY',   8_400, $lastMon->copy()->addDays(20), 'shared');
        $this->expense($user, 'Utilities', 'TEPCO electric bill',   'JPY',   9_200, $thisMon->copy()->addDays(11), 'shared');
        $this->expense($user, 'Utilities', 'SoftBank fibre',        'JPY',   6_500, $thisMon->copy()->addDays(2),  'shared');
        $this->expense($user, 'Food & Dining', 'Costco run (groceries)', 'JPY', 18_400, $thisMon->copy()->addDays(6),  'shared');

        // ── USD subscriptions ───────────────────────────────────────────────
        $this->expense($user, 'Entertainment', 'Netflix Premium',     'USD', 15.49, $lastMon->copy()->addDays(4),  'shared');
        $this->expense($user, 'Entertainment', 'Netflix Premium',     'USD', 15.49, $thisMon->copy()->addDays(4),  'shared');
        $this->expense($user, 'Entertainment', 'Spotify Individual',  'USD',  9.99, $thisMon->copy()->addDays(3));
        $this->expense($user, 'Utilities',     'ChatGPT Plus',        'USD', 20.00, $thisMon->copy()->addDays(5));
        $this->expense($user, 'Utilities',     'GitHub Pro',          'USD',  4.00, $thisMon->copy()->addDays(7));
        $this->expense($user, 'Utilities',     'iCloud+ 50 GB',       'USD',  0.99, $thisMon->copy()->addDays(2));

        // ── Vietnam trip — VND (late prev-month → early this-month) ─────────
        $this->expense($user, 'Travel',        'Hanoi hotel (5 nights)', 'VND', 4_500_000, $vietnam->copy()->addDays(0),  'shared');
        $this->expense($user, 'Transport',     'Grab — Noi Bai airport', 'VND',   350_000, $vietnam->copy()->addDays(0),  'shared');
        $this->expense($user, 'Food & Dining', 'Pho 10 Ly Quoc Su',      'VND',    80_000, $vietnam->copy()->addDays(1));
        $this->expense($user, 'Food & Dining', 'Banh mi street stall',   'VND',    45_000, $vietnam->copy()->addDays(2));
        $this->expense($user, 'Travel',        'Halong Bay day tour',    'VND', 1_200_000, $vietnam->copy()->addDays(3),  'shared');
        $this->expense($user, 'Food & Dining', 'Egg coffee — Giang',     'VND',    60_000, $vietnam->copy()->addDays(3));
        $this->expense($user, 'Shopping',      'Hanoi night market',     'VND',   250_000, $vietnam->copy()->addDays(4));
        $this->expense($user, 'Food & Dining', 'Ha Long beer (group)',   'VND',   180_000, $vietnam->copy()->addDays(4),  'shared');
        $this->expense($user, 'Transport',     'Grab — back to airport', 'VND',   280_000, $vietnam->copy()->addDays(5),  'shared');

        // ── Pending request awaiting Savinyu's approval ─────────────────────
        $this->expense($user, 'Food & Dining', 'Group dinner — Saizeriya', 'JPY',   3_800, $thisMon->copy()->addDays(9),  'pending');
        $this->expense($user, 'Shopping',      'Hanoi souvenirs (gifts)',  'VND', 850_000, $vietnam->copy()->addDays(5),  'pending');
    }

    private function seedSavinyu(User $user): void
    {
        $today    = Carbon::today();
        $thisMon  = $today->copy()->startOfMonth();
        $lastMon  = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $vietnam  = $today->copy()->startOfMonth()->subDays(16);

        // ── Tokyo personal — JPY (mostly previous month, before flying out) ──
        $this->expense($user, 'Food & Dining', 'Yoshinoya',             'JPY',     680, $lastMon->copy()->addDays(2));
        $this->expense($user, 'Food & Dining', 'Sukiya late-night',     'JPY',     720, $lastMon->copy()->addDays(5));
        $this->expense($user, 'Food & Dining', '7-Eleven onigiri',      'JPY',     380, $lastMon->copy()->addDays(7));
        $this->expense($user, 'Food & Dining', 'FamilyMart bento',      'JPY',     540, $lastMon->copy()->addDays(9));
        $this->expense($user, 'Food & Dining', 'Matsuya',               'JPY',     850, $lastMon->copy()->addDays(11));
        $this->expense($user, 'Food & Dining', 'Gusto family dinner',   'JPY',   1_420, $lastMon->copy()->addDays(13));
        $this->expense($user, 'Transport',     'Suica top-up',          'JPY',   3_000, $lastMon->copy()->addDays(2));
        $this->expense($user, 'Transport',     'Keio Line ticket',      'JPY',     320, $lastMon->copy()->addDays(8));
        $this->expense($user, 'Shopping',      'Don Quijote',           'JPY',   2_800, $lastMon->copy()->addDays(15));
        $this->expense($user, 'Shopping',      'MUJI basics',           'JPY',   4_200, $lastMon->copy()->addDays(17));
        $this->expense($user, 'Entertainment', 'Toho Cinema',           'JPY',   1_800, $lastMon->copy()->addDays(19));
        $this->expense($user, 'Entertainment', 'Steam game',            'USD',   29.99, $lastMon->copy()->addDays(21));

        // ── Shared household bills — Savinyu paid ───────────────────────────
        $this->expense($user, 'Utilities', 'Tokyo Gas',                'JPY',   4_800, $lastMon->copy()->addDays(22), 'shared');
        $this->expense($user, 'Utilities', 'Tokyo Gas',                'JPY',   5_200, $thisMon->copy()->addDays(8),  'shared');
        $this->expense($user, 'Utilities', 'Tokyo Water Bureau',       'JPY',   3_400, $thisMon->copy()->addDays(9),  'shared');
        $this->expense($user, 'Utilities', 'NHK fee',                  'JPY',   2_450, $lastMon->copy()->addDays(24), 'shared');
        $this->expense($user, 'Food & Dining', 'Costco — bulk groceries', 'JPY', 15_200, $lastMon->copy()->addDays(16), 'shared');

        // ── USD subscriptions (Savinyu's personal) ──────────────────────────
        $this->expense($user, 'Utilities', 'Adobe Creative Cloud',  'USD', 54.99, $lastMon->copy()->addDays(6));
        $this->expense($user, 'Utilities', 'Adobe Creative Cloud',  'USD', 54.99, $thisMon->copy()->addDays(6));
        $this->expense($user, 'Utilities', 'iCloud+ 200 GB',        'USD',  2.99, $thisMon->copy()->addDays(3));

        // ── Vietnam trip — VND ──────────────────────────────────────────────
        $this->expense($user, 'Food & Dining', 'Pho 25 restaurant',     'VND',    90_000, $vietnam->copy()->addDays(1));
        $this->expense($user, 'Food & Dining', 'Hotel breakfast buffet', 'VND',   320_000, $vietnam->copy()->addDays(2));
        $this->expense($user, 'Transport',     'Grab around Hanoi',     'VND',    80_000, $vietnam->copy()->addDays(3));
        $this->expense($user, 'Healthcare',    'Spa massage — Old Quarter', 'VND', 600_000, $vietnam->copy()->addDays(3));
        $this->expense($user, 'Food & Dining', 'Bun cha Obama',         'VND',   120_000, $vietnam->copy()->addDays(4));
        $this->expense($user, 'Transport',     'Group taxi to Halong',  'VND',   450_000, $vietnam->copy()->addDays(3),  'shared');

        // ── Pending request awaiting Vaibhav's approval ─────────────────────
        $this->expense($user, 'Travel', 'Halong Bay overnight cruise',  'VND', 1_800_000, $vietnam->copy()->addDays(2),  'pending');

        // ── India leg — INR (last ~7 days, currently traveling) ─────────────
        $indiaStart = $today->copy()->subDays(7);

        $this->expense($user, 'Travel',        'Taj Hotel Mumbai (5 nights)', 'INR', 22_500, $indiaStart->copy()->addDays(0));
        $this->expense($user, 'Transport',     'Uber Mumbai',                 'INR',    420, $indiaStart->copy()->addDays(1));
        $this->expense($user, 'Transport',     'IndiGo Mumbai → Delhi',       'INR',  6_800, $indiaStart->copy()->addDays(2));
        $this->expense($user, 'Food & Dining', 'Lassi cafe',                  'INR',    180, $indiaStart->copy()->addDays(2));
        $this->expense($user, 'Shopping',      'Big Bazaar shopping',         'INR',  1_850, $indiaStart->copy()->addDays(3));
        $this->expense($user, 'Healthcare',    'Apollo Pharmacy',             'INR',    520, $indiaStart->copy()->addDays(3));
        $this->expense($user, 'Food & Dining', "Karim's old Delhi",           'INR',    890, $indiaStart->copy()->addDays(4));
        $this->expense($user, 'Transport',     'Auto rickshaw',               'INR',    150, $indiaStart->copy()->addDays(5));
        $this->expense($user, 'Utilities',     'Tata Sky DTH',                'INR',    599, $indiaStart->copy()->addDays(5));
        $this->expense($user, 'Entertainment', 'BookMyShow movie',            'INR',    450, $indiaStart->copy()->addDays(6));
        $this->expense($user, 'Food & Dining', 'Swiggy delivery',             'INR',    680, $indiaStart->copy()->addDays(6));
        $this->expense($user, 'Transport',     'Delhi metro',                 'INR',     40, $indiaStart->copy()->addDays(7));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Insert one expense for $user, converting display amount → minor units and
     * computing the JPY-base equivalent from the offline DEMO rate table.
     *
     * $displayAmount is in *natural* units (e.g. 1250 yen, 15.49 dollars,
     * 850000 đồng). The DB stores it ×100 (minor units), exactly as the
     * runtime store flow does in DashboardController::store().
     */
    private function expense(
        User $user,
        string $category,
        string $description,
        string $currency,
        float $displayAmount,
        Carbon $date,
        string $sharedStatus = Expense::SHARED_PERSONAL,
    ): Expense {
        $rate                = self::RATES[$currency] ?? 1.0;
        $originalAmountMinor = (int) round($displayAmount * 100);
        $baseAmountMinor     = (int) round($originalAmountMinor * $rate);

        return $user->expenses()->create([
            'amount'            => $originalAmountMinor,
            'currency'          => $currency,
            'original_amount'   => $originalAmountMinor,
            'original_currency' => $currency,
            'base_amount'       => $baseAmountMinor,
            'exchange_rate'     => $rate,
            'description'       => $description,
            'category'          => $category,
            'expense_date'      => $date->format('Y-m-d'),
            'shared_status'     => $sharedStatus,
        ]);
    }
}
