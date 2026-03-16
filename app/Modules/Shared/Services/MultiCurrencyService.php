<?php

namespace App\Modules\Shared\Services;

use Illuminate\Support\Facades\Cookie;

/**
 * Multi-Currency Service
 * 
 * Handles currency detection and conversion:
 * - Default: INR
 * - Supported: USD, EUR, GBP, AED, SGD
 * - Uses geo-IP detection for defaults
 * - Persistent choice via cookies and user preferences
 */
class MultiCurrencyService
{
    protected const SUPPORTED_CURRENCIES = [
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'country' => 'IN', 'default' => true],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'country' => 'US'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'country' => 'EU'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'country' => 'GB'],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ', 'country' => 'AE'],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'country' => 'SG'],
    ];

    protected const EXCHANGE_RATES = [
        'INR' => 1.0,
        'USD' => 0.012,      // 1 INR = 0.012 USD
        'EUR' => 0.011,      // 1 INR = 0.011 EUR
        'GBP' => 0.0095,     // 1 INR = 0.0095 GBP
        'AED' => 0.044,      // 1 INR = 0.044 AED
        'SGD' => 0.016,      // 1 INR = 0.016 SGD
    ];

    protected string $currentCurrency;

    public function __construct(protected string $userCountry = 'IN')
    {
        $this->currentCurrency = $this->detectCurrency();
    }

    /**
     * Detect currency based on user location
     */
    protected function detectCurrency(): string
    {
        // Check user preference cookie
        if ($cookie = request()->cookie('currency')) {
            return $cookie;
        }

        // Geo-IP detection
        $countryCodeToCurrency = [
            'IN' => 'INR',
            'US' => 'USD',
            'GB' => 'GBP',
            'DE' => 'EUR',
            'FR' => 'EUR',
            'IT' => 'EUR',
            'ES' => 'EUR',
            'AE' => 'AED',
            'SG' => 'SGD',
            'AU' => 'USD', // Fallback to USD
            'CA' => 'USD',
            'NZ' => 'USD',
        ];

        $currency = $countryCodeToCurrency[$this->userCountry] ?? 'INR';
        return $currency;
    }

    /**
     * Get current currency
     */
    public function getCurrency(): string
    {
        return $this->currentCurrency;
    }

    /**
     * Set currency
     */
    public function setCurrency(string $currency): self
    {
        if (!isset(self::SUPPORTED_CURRENCIES[$currency])) {
            throw new \InvalidArgumentException("Currency {$currency} is not supported");
        }

        $this->currentCurrency = $currency;
        Cookie::queue('currency', $currency, 365 * 24 * 60); // 1 year cookie

        return $this;
    }

    /**
     * Convert price from INR to target currency
     */
    public function convertPrice(float $priceInINR, string $targetCurrency = null): float
    {
        $targetCurrency = $targetCurrency ?? $this->currentCurrency;

        if (!isset(self::EXCHANGE_RATES[$targetCurrency])) {
            return $priceInINR;
        }

        return $priceInINR * self::EXCHANGE_RATES[$targetCurrency];
    }

    /**
     * Format price with currency symbol
     */
    public function formatPrice(float $priceInINR, string $currency = null): string
    {
        $currency = $currency ?? $this->currentCurrency;
        $converted = $this->convertPrice($priceInINR, $currency);
        $config = self::SUPPORTED_CURRENCIES[$currency];
        $symbol = $config['symbol'];

        // Format based on currency
        return match ($currency) {
            'INR' => $symbol . ' ' . number_format($converted, 0, '.', ''),
            'USD', 'AED', 'SGD' => $symbol . number_format($converted, 2),
            'EUR', 'GBP' => $symbol . number_format($converted, 2),
            default => $symbol . ' ' . number_format($converted, 2),
        };
    }

    /**
     * Get all supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return self::SUPPORTED_CURRENCIES;
    }

    /**
     * Get currency info
     */
    public function getCurrencyInfo(string $code): array
    {
        return self::SUPPORTED_CURRENCIES[$code] ?? [];
    }

    /**
     * Get hreflang tags for multi-currency support
     */
    public function getHrefLangTags(string $baseUrl): array
    {
        $tags = [];

        foreach (self::SUPPORTED_CURRENCIES as $code => $config) {
            $countryCode = strtolower($config['country']);
            $tags[] = [
                'rel' => 'alternate',
                'hreflang' => 'en-' . $countryCode,
                'href' => $baseUrl . '?currency=' . $code,
            ];
        }

        return $tags;
    }

    /**
     * Get language-specific currency info for SEO
     */
    public function getSeoMetadata(): array
    {
        $currency = self::SUPPORTED_CURRENCIES[$this->currentCurrency];

        return [
            'currency_code' => $this->currentCurrency,
            'currency_symbol' => $currency['symbol'],
            'currency_name' => $currency['name'],
            'default_locale' => 'en-' . strtolower($currency['country']),
        ];
    }
}
