<?php

namespace App\Utils;


class CurrencyUtil
{
    public static function formatCurrencyToBr(string $amount): string
    {
        $formatted = str_replace(',', '.', $amount);
        return number_format($formatted, 2, ',', '');
    }

    public static function formatCurrencyToDb(string $amount): string
    {
        $formatted = str_replace('.', '', $amount);
        $formatted = str_replace(',', '.', $formatted);
        return $formatted;
    }
}
