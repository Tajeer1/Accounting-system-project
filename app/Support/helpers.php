<?php

use App\Models\Setting;

if (! function_exists('money')) {
    function money($amount, bool $withSymbol = true): string
    {
        $decimals = (int) Setting::get('currency_decimals', 3);
        $formatted = number_format((float) $amount, $decimals);
        if (! $withSymbol) {
            return $formatted;
        }
        $symbol = Setting::get('currency_symbol', 'ر.ع');
        return $formatted . ' ' . $symbol;
    }
}

if (! function_exists('short_money')) {
    function short_money($amount): string
    {
        $amount = (float) $amount;
        $symbol = Setting::get('currency_symbol', 'ر.ع');
        if (abs($amount) >= 1_000_000) {
            return number_format($amount / 1_000_000, 1) . 'م ' . $symbol;
        }
        if (abs($amount) >= 1_000) {
            return number_format($amount / 1_000, 1) . 'ك ' . $symbol;
        }
        return number_format($amount, 0) . ' ' . $symbol;
    }
}
