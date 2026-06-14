<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    // Supported display currencies
    public const CURRENCIES = ['RUB', 'USD', 'EUR', 'GBP', 'CNY', 'UAH', 'KZT', 'BYN', 'TRY'];

    // Cache key and TTL
    private const CACHE_KEY        = 'exchange_rates_usd';
    private const BACKUP_CACHE_KEY = 'exchange_rates_usd_backup';
    private const TTL              = 6 * 3600;
    private const BACKUP_TTL       = 30 * 24 * 3600; // 30 days

    /**
     * Returns rates relative to USD: ['RUB' => 90.5, 'EUR' => 0.92, ...]
     * Refreshes automatically when cache expires.
     */
    public static function getRates(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL, function () {
            return self::fetch();
        });
    }

    /**
     * Force refresh even if cache is not expired.
     */
    public static function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);
        $rates = self::fetch();
        Cache::put(self::CACHE_KEY, $rates, self::TTL);
        return $rates;
    }

    /**
     * Convert amount from one currency to another.
     * Returns original amount if conversion is not possible.
     */
    public static function convert(float $amount, string $from, string $to, array $rates = null): float
    {
        if ($from === $to || $amount == 0) {
            return $amount;
        }

        $rates ??= self::getRates();

        $rateFrom = $rates[$from] ?? null;
        $rateTo   = $rates[$to]   ?? null;

        if (!$rateFrom || !$rateTo) {
            return $amount; // fallback: no conversion
        }

        // Convert: from → USD → to
        return $amount * ($rateTo / $rateFrom);
    }

    /**
     * Returns timestamp of last successful rate fetch (from cache metadata).
     */
    public static function lastUpdatedAt(): ?int
    {
        return Cache::get(self::CACHE_KEY . '_ts');
    }

    private static function fetch(): array
    {
        try {
            $response = Http::timeout(5)
                ->get('https://open.er-api.com/v6/latest/USD');

            if ($response->ok()) {
                $rates = $response->json('rates') ?? [];
                if (!empty($rates)) {
                    Cache::put(self::CACHE_KEY . '_ts', time(), self::TTL + 3600);
                    // Persist last successful fetch as long-lived backup
                    Cache::put(self::BACKUP_CACHE_KEY, $rates, self::BACKUP_TTL);
                    return $rates;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ExchangeRate fetch failed: ' . $e->getMessage());
        }

        // Fallback: use last persisted rates from previous successful fetch
        $backup = Cache::get(self::BACKUP_CACHE_KEY);
        if (!empty($backup)) {
            Log::info('ExchangeRate: using backup rates from cache');
            return $backup;
        }

        // Last resort: static approximate rates (only if never fetched before)
        return [
            'USD' => 1.0,
            'RUB' => 90.0,
            'EUR' => 0.92,
            'GBP' => 0.79,
            'CNY' => 7.25,
            'UAH' => 41.0,
            'KZT' => 450.0,
            'BYN' => 3.25,
            'TRY' => 32.0,
        ];
    }
}
