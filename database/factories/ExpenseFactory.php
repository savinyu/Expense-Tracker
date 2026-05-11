<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    // ── Demo exchange rates (JPY as the base currency) ────────────────────────
    // Hardcoded so seeding never hits the live API.
    //   original_amount (USD) × 155.0 → base_amount (JPY)
    //   original_amount (INR) × 1.8   → base_amount (JPY)
    private const DEMO_RATES = [
        'JPY' => 1.0,
        'USD' => 155.0,
        'INR' => 1.8,
    ];

    // ── Category catalogue ────────────────────────────────────────────────────
    // jpy_range is in DISPLAY yen (¥).  The factory multiplies by 100 for minor
    // units, then divides by the exchange rate to get the original-currency amount.
    private const CATALOGUE = [
        'Food & Dining' => [
            'vendors'   => [
                'Sukiya', "McDonald's", 'Starbucks', '7-Eleven', 'Yoshinoya',
                'FamilyMart', 'Saizeriya', 'Matsuya', 'CoCo Ichibanya', 'Lawson Café',
                'Gusto', 'Ootoya', 'Freshness Burger', 'Hotto Motto',
            ],
            'jpy_range' => [500, 3500],
        ],
        'Transport' => [
            'vendors'   => [
                'Suica (IC Card)', 'Uber', 'Monthly Bus Pass', 'Shinkansen Ticket',
                'Taxi', 'Lime Scooter', 'Airport Express', 'Keio Line',
            ],
            'jpy_range' => [200, 8000],
        ],
        'Shopping' => [
            'vendors'   => [
                'Amazon Japan', 'UNIQLO', 'Daiso', 'Don Quijote', 'MUJI',
                'Yodobashi Camera', 'Bic Camera', 'GU', 'Tokyu Hands',
            ],
            'jpy_range' => [500, 18000],
        ],
        'Healthcare' => [
            'vendors'   => [
                'Matsumoto Kiyoshi', 'Clinic Visit', 'Dental Checkup',
                'Eye Exam', "Gold's Gym", 'Wellbe Pharmacy', 'Hospital Co-pay',
            ],
            'jpy_range' => [1000, 12000],
        ],
        'Entertainment' => [
            'vendors'   => [
                'Toho Cinema', 'Steam', 'Nintendo eShop', 'Big Echo Karaoke',
                'Concert Ticket', 'Tokyo Dome Event', 'Escape Room',
            ],
            'jpy_range' => [800, 8000],
        ],
        'Utilities' => [
            'vendors'   => [
                'Netflix', 'Spotify', 'Adobe Creative Cloud', 'GitHub Pro',
                'ChatGPT Plus', 'iCloud+ 50 GB', 'Amazon Prime',
                'SoftBank Mobile', 'NHK Fee', 'TEPCO Electric Bill',
            ],
            'jpy_range' => [500, 6000],
        ],
        'Travel' => [
            'vendors'   => [
                'APA Hotel', 'Dormy Inn', 'Airbnb', 'JR Pass',
                'Rakuten Travel', 'Expedia', 'Budget Car Rental',
            ],
            'jpy_range' => [5000, 35000],
        ],
    ];

    // ── Weighted currency pool (JPY 50 %, USD 30 %, INR 20 %) ─────────────────
    private const CURRENCY_POOL = [
        'JPY', 'JPY', 'JPY', 'JPY', 'JPY',
        'USD', 'USD', 'USD',
        'INR', 'INR',
    ];

    // ── Core definition ───────────────────────────────────────────────────────

    public function definition(): array
    {
        $category         = fake()->randomElement(array_keys(self::CATALOGUE));
        $meta             = self::CATALOGUE[$category];
        $originalCurrency = self::weightedCurrency();

        [$originalAmountMinor, $baseAmountMinor, $rate] =
            self::calculateAmounts($meta['jpy_range'], $originalCurrency);

        return [
            'user_id'           => User::factory(),
            'amount'            => $originalAmountMinor,
            'currency'          => $originalCurrency,
            'original_amount'   => $originalAmountMinor,
            'original_currency' => $originalCurrency,
            'base_amount'       => $baseAmountMinor,
            'exchange_rate'     => $rate,
            'description'       => fake()->randomElement($meta['vendors']),
            'category'          => $category,
            // Default spread: last 30 days so the demo chart looks full
            'expense_date'      => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    /**
     * Pin category, vendor, and recomputed amounts from the catalogue.
     */
    public function forCategory(string $category): static
    {
        $meta             = self::CATALOGUE[$category];
        $originalCurrency = self::weightedCurrency();

        [$originalAmountMinor, $baseAmountMinor, $rate] =
            self::calculateAmounts($meta['jpy_range'], $originalCurrency);

        return $this->state(fn () => [
            'category'          => $category,
            'description'       => fake()->randomElement($meta['vendors']),
            'amount'            => $originalAmountMinor,
            'currency'          => $originalCurrency,
            'original_amount'   => $originalAmountMinor,
            'original_currency' => $originalCurrency,
            'base_amount'       => $baseAmountMinor,
            'exchange_rate'     => $rate,
        ]);
    }

    /**
     * Override the currency on an already-built state, recalculating amounts.
     */
    public function withCurrency(string $currency): static
    {
        return $this->state(function (array $attrs) use ($currency) {
            $rate                = self::DEMO_RATES[$currency] ?? 1.0;
            $baseJpyMinor        = $attrs['base_amount']
                ?? fake()->numberBetween(1000, 20000) * 100;
            $originalAmountMinor = (int) round($baseJpyMinor / $rate);
            $baseAmountMinor     = (int) round($originalAmountMinor * $rate);

            return [
                'currency'          => $currency,
                'original_currency' => $currency,
                'amount'            => $originalAmountMinor,
                'original_amount'   => $originalAmountMinor,
                'base_amount'       => $baseAmountMinor,
                'exchange_rate'     => $rate,
            ];
        });
    }

    /** Expenses spread across the current calendar month up to today. */
    public function currentMonth(): static
    {
        return $this->state(fn () => [
            'expense_date' => fake()->dateTimeBetween(
                now()->copy()->startOfMonth(),
                now()
            )->format('Y-m-d'),
        ]);
    }

    /** Expenses spread across the entire previous calendar month. */
    public function previousMonth(): static
    {
        return $this->state(fn () => [
            'expense_date' => fake()->dateTimeBetween(
                now()->copy()->subMonth()->startOfMonth(),
                now()->copy()->subMonth()->endOfMonth()
            )->format('Y-m-d'),
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Generate [originalAmountMinor, baseAmountMinor, rate] for a given jpy_range
     * and target original currency.
     *
     * @param  int[]  $jpyRange  [min, max] in display yen
     * @return array{int, int, float}
     */
    private static function calculateAmounts(array $jpyRange, string $currency): array
    {
        $rate                = self::DEMO_RATES[$currency] ?? 1.0;
        $baseJpyMinor        = fake()->numberBetween(...$jpyRange) * 100;
        $originalAmountMinor = (int) round($baseJpyMinor / $rate);
        $baseAmountMinor     = (int) round($originalAmountMinor * $rate);

        return [$originalAmountMinor, $baseAmountMinor, $rate];
    }

    private static function weightedCurrency(): string
    {
        return self::CURRENCY_POOL[array_rand(self::CURRENCY_POOL)];
    }
}
