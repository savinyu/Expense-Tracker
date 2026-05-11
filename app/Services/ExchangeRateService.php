<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    private const API_URL = 'https://open.er-api.com/v6/latest';

    /**
     * Fetch the conversion rate from $from → $to.
     * Results are cached for 1 hour. Falls back to 1.0 on any failure.
     */
    public function getRate(string $from, string $to): float
    {
        if ($from === $to) {
            return 1.0;
        }

        return (float) Cache::remember("fx_{$from}_{$to}", 3600, function () use ($from, $to) {
            try {
                $response = Http::timeout(10)
                    ->withoutVerifying()
                    ->get(self::API_URL . '/' . $from);

                if ($response->failed()) {
                    Log::error("ExchangeRateService: HTTP {$response->status()} for {$from}→{$to}", [
                        'body' => $response->body(),
                    ]);
                    return 1.0;
                }

                if ($response->json('result') !== 'success') {
                    Log::error("ExchangeRateService: API returned non-success result for {$from}→{$to}", [
                        'result' => $response->json('result'),
                        'body'   => $response->body(),
                    ]);
                    return 1.0;
                }

                $rate = $response->json('rates.' . $to);

                if ($rate === null) {
                    Log::error("ExchangeRateService: rate for {$to} not found in response for base {$from}");
                    return 1.0;
                }

                return (float) $rate;
            } catch (\Throwable $e) {
                Log::error('ExchangeRateService: request exception for ' . $from . '→' . $to . ': ' . $e->getMessage());
                return 1.0;
            }
        });
    }

    /**
     * Convert a minor-unit amount from one currency to another.
     * Returns the converted amount, also in minor units.
     */
    public function convert(int $amountMinorUnits, string $from, string $to): array
    {
        $rate        = $this->getRate($from, $to);
        $baseAmount  = (int) round($amountMinorUnits * $rate);

        return [
            'base_amount'   => $baseAmount,
            'exchange_rate' => $rate,
        ];
    }
}
