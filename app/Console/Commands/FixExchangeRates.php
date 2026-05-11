<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class FixExchangeRates extends Command
{
    protected $signature = 'expenses:fix-exchange-rates
                            {--dry-run : Preview affected records without saving}';

    protected $description = 'Backfill correct base_amount and exchange_rate for expenses stored with a fallback rate of 1.0';

    public function handle(ExchangeRateService $fx): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be saved.');
            $this->newLine();
        }

        // Load expenses where the API fallback was used:
        // original_currency differs from user's base AND rate is still 1.0
        $expenses = Expense::with('user')
            ->where('exchange_rate', 1.0)
            ->get()
            ->filter(fn (Expense $e) => $e->original_currency !== ($e->user->default_currency ?? 'JPY'));

        $total = $expenses->count();

        if ($total === 0) {
            $this->info('Nothing to fix — all records already have correct exchange rates.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} expense(s) with stale exchange rates.");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->setMessage('Starting…');
        $bar->start();

        $fixed    = 0;
        $skipped  = 0;
        $apiCache = []; // avoid redundant API calls for the same currency pair

        foreach ($expenses as $expense) {
            $from = $expense->original_currency;
            $to   = $expense->user->default_currency ?? 'JPY';
            $pair = "{$from}_{$to}";

            $bar->setMessage("Expense #{$expense->id}: {$from} → {$to}");

            if (!array_key_exists($pair, $apiCache)) {
                $apiCache[$pair] = $fx->getRate($from, $to);
            }

            $rate = $apiCache[$pair];

            if ($rate == 1.0) {
                // API still returning fallback — cannot fix reliably
                $skipped++;
                $bar->advance();
                continue;
            }

            $baseAmount = (int) round($expense->original_amount * $rate);

            if (!$dryRun) {
                $expense->update([
                    'base_amount'   => $baseAmount,
                    'exchange_rate' => $rate,
                ]);
            }

            $fixed++;
            $bar->advance();
        }

        $bar->setMessage('Done.');
        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Records found',                $total],
                ['Fixed' . ($dryRun ? ' (dry)' : ''), $fixed],
                ['Skipped (API unavailable)',    $skipped],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('Dry run complete — run without --dry-run to apply changes.');
        }

        return $skipped > 0 ? self::FAILURE : self::SUCCESS;
    }
}
