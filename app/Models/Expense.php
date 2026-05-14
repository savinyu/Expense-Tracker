<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'amount', 'currency', 'original_amount', 'original_currency', 'base_amount', 'exchange_rate', 'description', 'category', 'expense_date', 'shared_status'])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    /** Supported currency codes (ISO 4217). */
    public const CURRENCIES = ['JPY', 'USD', 'EUR', 'GBP', 'AUD', 'CAD', 'SGD', 'INR', 'VND'];

    /** Shared expense lifecycle. */
    public const SHARED_PERSONAL = 'personal';
    public const SHARED_PENDING  = 'pending';
    public const SHARED_SHARED   = 'shared';
    public const SHARED_STATUSES = [self::SHARED_PERSONAL, self::SHARED_PENDING, self::SHARED_SHARED];

    /** Currency symbol map for display. */
    private const SYMBOLS = [
        'JPY' => '¥',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'AUD' => 'A$',
        'CAD' => 'C$',
        'SGD' => 'S$',
        'INR' => '₹',
        'VND' => '₫',
    ];

    /** Currencies with no minor units (display 0 decimal places). */
    private const ZERO_DECIMAL = ['JPY', 'VND'];

    protected function casts(): array
    {
        return [
            'amount'          => 'integer',
            'original_amount' => 'integer',
            'base_amount'     => 'integer',
            'exchange_rate'   => 'float',
            'expense_date'    => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format a raw minor-unit amount for a given currency.
     * Handles zero-decimal currencies (JPY → no cents).
     */
    public static function formatAmount(int $amount, string $currency): string
    {
        $symbol   = self::SYMBOLS[$currency] ?? ($currency . ' ');
        $decimals = in_array($currency, self::ZERO_DECIMAL) ? 0 : 2;
        return $symbol . number_format($amount / 100, $decimals);
    }

    public function formattedAmount(): string
    {
        return self::formatAmount($this->amount, $this->currency);
    }

    /**
     * Return just the display symbol for a currency code, or the code itself
     * (with a trailing space) if no symbol is registered.
     */
    public static function symbol(string $currency): string
    {
        return self::SYMBOLS[$currency] ?? ($currency . ' ');
    }

    /**
     * Dropdown-ready label: "JPY (¥)", "INR (₹)", etc.
     */
    public static function label(string $currency): string
    {
        return $currency . ' (' . self::symbol($currency) . ')';
    }
}
