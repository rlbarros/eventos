<?php

namespace App\Utils;



class DateUtil
{
    public static function formatDateToBr(string | null $date): string
    {
        if (empty($date)) {
            return '';
        }
        return date('d/m/Y', strtotime($date));
    }

    public static function formatDateTimeToBr(string | null $dateTime): string
    {
        if (empty($dateTime)) {
            return '';
        }
        return date('d/m/Y H:i', strtotime($dateTime));
    }

    public static function formatDateToEn(string | null $date): string
    {
        if (empty($date)) {
            return '';
        }
        return date('Y-m-d', strtotime($date));
    }
}
