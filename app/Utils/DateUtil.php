<?php

namespace App\Utils;



class DateUtil
{
    public static function formatDateToBr(string $date): string
    {
        return date('d/m/Y', strtotime($date));
    }

    public static function formatDateToEn(string $date): string
    {
        return date('Y-m-d', strtotime($date));
    }
}
