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

    public static function functionAbreviation(string $function): string
    {
        switch ($function) {
            case 'Pastor':
                return 'PR.';
            case 'Evangelista':
                return 'EV.';
            case 'Pregador de Conferência':
                return 'OBC.';
            default:
                return '';
        }
    }
}
