<?php

namespace App\Utils;

/**
 * Links click-to-chat (wa.me): nada é enviado automaticamente; o organizador
 * abre o link e confirma o envio no próprio WhatsApp.
 */
class WhatsAppUtil
{
    /**
     * Normaliza um telefone brasileiro para o formato internacional só com
     * dígitos (55 + DDD + número). Retorna null se não parecer um telefone válido.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '' || $digits === null) {
            return null;
        }

        if (str_starts_with($digits, '55') && in_array(strlen($digits), [12, 13], true)) {
            return $digits;
        }

        // DDD + 8 dígitos (fixo) ou DDD + 9 dígitos (celular)
        if (in_array(strlen($digits), [10, 11], true)) {
            return '55' . $digits;
        }

        return null;
    }

    /** URL wa.me com o texto já preenchido, ou null se o telefone for inválido. */
    public static function link(?string $phone, string $message): ?string
    {
        $normalized = self::normalizePhone($phone);

        if ($normalized === null) {
            return null;
        }

        return 'https://wa.me/' . $normalized . '?text=' . rawurlencode($message);
    }
}
