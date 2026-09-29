<?php

namespace App\Services\Messaging;

use App\Models\EventTrip;
use App\Models\Person;
use App\Utils\DateUtil;

/**
 * Textos das mensagens de uma viagem: transportador, motorista e passageiros.
 */
class TripMessages
{
    public static function forTransporter(EventTrip $trip): string
    {
        $driver = $trip->event_driver;

        return "Olá" . self::greetingName($trip->transporter_name) . "! Segue a viagem do evento {$trip->event->name}:\n"
            . self::route($trip)
            . "Motorista: {$driver->name} ({$driver->phone})\n"
            . "Veículo: {$driver->vehicle}\n"
            . "Passageiros: {$trip->event_trip_participants->count()}\n"
            . self::contactLine($trip);
    }

    public static function forDriver(EventTrip $trip): string
    {
        $passengers = $trip->event_trip_participants
            ->map(fn($tp) => $tp->person)
            ->filter()
            ->sortBy('name')
            ->values();

        $list = $passengers->isEmpty()
            ? "(nenhum passageiro cadastrado ainda)\n"
            : $passengers->map(fn(Person $p, int $i) => ($i + 1) . '. ' . $p->name . ($p->phone ? " - {$p->phone}" : ''))->implode("\n") . "\n";

        return "Olá, {$trip->event_driver->name}! Sua viagem do evento {$trip->event->name}:\n"
            . self::route($trip)
            . "Passageiros ({$passengers->count()}):\n" . $list
            . self::contactLine($trip);
    }

    public static function forPassenger(EventTrip $trip, Person $person): string
    {
        $driver = $trip->event_driver;

        return "Olá, {$person->name}! Sua viagem para o evento {$trip->event->name}:\n"
            . self::route($trip)
            . "Motorista: {$driver->name} ({$driver->phone})\n"
            . "Veículo: {$driver->vehicle}\n"
            . self::contactLine($trip);
    }

    private static function route(EventTrip $trip): string
    {
        return "Origem: {$trip->from}\n"
            . "Partida: " . DateUtil::formatDateTimeToBr($trip->start_date) . "\n"
            . "Destino: {$trip->to}\n"
            . "Chegada prevista: " . DateUtil::formatDateTimeToBr($trip->end_date) . "\n";
    }

    private static function contactLine(EventTrip $trip): string
    {
        $event = $trip->event;

        if (empty($event->contact_name) && empty($event->contact_phone)) {
            return '';
        }

        return "Dúvidas: " . trim("{$event->contact_name} {$event->contact_phone}") . "\n";
    }

    private static function greetingName(?string $name): string
    {
        return empty($name) ? '' : ", {$name}";
    }
}
