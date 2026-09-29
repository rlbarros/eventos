<?php

namespace App\Services\Messaging;

use Carbon\Carbon;

/**
 * .ics mínimo (RFC 5545) de um evento de dia inteiro cobrindo a hospedagem.
 * METHOD:PUBLISH: o Gmail oferece "Adicionar à agenda" sem exigir convite/RSVP.
 */
class IcsBuilder
{
    public static function lodging(LodgingNotice $notice, ?Carbon $now = null): string
    {
        $now = ($now ?? now())->copy()->utc();
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//IEA Brasil//Eventos//PT-BR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $notice->reservationNumber() . '@eventos.ieabrasil',
            'DTSTAMP:' . $now->format('Ymd\THis\Z'),
            // dia inteiro; DTEND é exclusivo, por isso saída + 1 dia
            'DTSTART;VALUE=DATE:' . $notice->checkIn()->format('Ymd'),
            'DTEND;VALUE=DATE:' . $notice->checkOut()->copy()->addDay()->format('Ymd'),
            'SUMMARY:' . self::escape('Hospedagem - ' . $notice->eventName()),
            'LOCATION:' . self::escape($notice->fullAddress()),
            'DESCRIPTION:' . self::escape(self::description($notice)),
            'STATUS:CONFIRMED',
            'TRANSP:TRANSPARENT',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", array_map([self::class, 'fold'], $lines)) . "\r\n";
    }

    private static function description(LodgingNotice $notice): string
    {
        $parts = ["Participante: {$notice->personName()}"];

        if ($notice->roomName() !== '') {
            $parts[] = 'Quarto: ' . $notice->roomName();
        }

        return implode("\n", $parts);
    }

    private static function escape(string $text): string
    {
        return str_replace(["\\", ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $text);
    }

    /** Linhas com mais de 75 octetos são quebradas com CRLF + espaço. */
    private static function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $chunks = [];
        $current = '';

        foreach (mb_str_split($line) as $char) {
            $limit = $chunks === [] ? 75 : 74;
            if (strlen($current . $char) > $limit) {
                $chunks[] = $current;
                $current = '';
            }
            $current .= $char;
        }
        $chunks[] = $current;

        return implode("\r\n ", $chunks);
    }
}
