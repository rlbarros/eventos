<?php

namespace App\Utils;


class CurrencyUtil
{
    public static function formatCurrencyToBr(string | null $amount, bool $prefix = false): string
    {
        if (empty($amount)) {
            $amount = '0.00';
        }
        $formatted = str_replace(',', '.', $amount);

        if ($prefix) {
            return 'R$ ' . number_format($formatted, 2, ',', '');
        }

        return number_format($formatted, 2, ',', '');
    }

    public static function formatCurrencyToDb(string | null $amount): string
    {
        if (empty($amount)) {
            return '';
        }
        $formatted = str_replace('.', '', $amount);
        $formatted = str_replace(',', '.', $formatted);
        return $formatted;
    }
}
