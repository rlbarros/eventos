<?php

namespace App\Utils;

class DescriptorUtil
{
    public static function describe(string | null $var1, string | null $var2): string
    {
        if (empty($var1) && empty($var2)) {
            return '';
        } else if (!empty($var1) && !empty($var2)) {
            return $var1 . ' - ' . $var2;
        } else if (!empty($var2)) {
            return $var2;
        } else if (!empty($var1)) {
            return $var1;
        } else {
            return '';
        }
    }
}
