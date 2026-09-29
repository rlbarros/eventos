<?php

namespace App\Services\Messaging;

use App\Models\EventParticipantAllocation;
use App\Utils\DateUtil;
use Carbon\Carbon;

/**
 * Dados e textos do aviso de hospedagem de um participante alocado.
 */
class LodgingNotice
{
    public function __construct(public readonly EventParticipantAllocation $allocation) {}

    /** Carrega as relações necessárias (evita N+1 e relações ausentes). */
    public static function for(EventParticipantAllocation $allocation): self
    {
        $allocation->loadMissing(['event.event_site.city', 'event.event_site.state', 'person', 'event_site_room.event_site_room_type', 'event_site_room_type']);

        return new self($allocation);
    }

    /** Só há o que avisar quando o quarto já foi definido. */
    public function isReady(): bool
    {
        return !empty($this->allocation->event_site_room_id);
    }

    public function personName(): string
    {
        return $this->allocation->person->name;
    }

    public function eventName(): string
    {
        return $this->allocation->event->name;
    }

    public function siteName(): string
    {
        return $this->allocation->event->event_site->name;
    }

    public function roomName(): string
    {
        return $this->allocation->event_site_room?->name ?? '';
    }

    public function roomTypeName(): string
    {
        return $this->allocation->event_site_room_type?->name
            ?? $this->allocation->event_site_room?->event_site_room_type?->name
            ?? '';
    }

    public function checkIn(): Carbon
    {
        return Carbon::parse($this->allocation->event->start_date)->startOfDay();
    }

    public function checkOut(): Carbon
    {
        return Carbon::parse($this->allocation->event->end_date)->startOfDay();
    }

    public function street(): string
    {
        $site = $this->allocation->event->event_site;

        return trim(implode(', ', array_filter([$site->address, $site->number, $site->complement])));
    }

    public function city(): string
    {
        return (string) $this->allocation->event->event_site->city?->name;
    }

    public function state(): string
    {
        $state = $this->allocation->event->event_site->state;

        return (string) ($state->code ?? '');
    }

    public function zipCode(): string
    {
        return (string) $this->allocation->event->event_site->zip_code;
    }

    /** Endereço em uma linha, para o texto do WhatsApp e o LOCATION do .ics. */
    public function fullAddress(): string
    {
        return implode(' - ', array_filter([
            $this->siteName(),
            $this->street(),
            trim($this->city() . ($this->state() ? '/' . $this->state() : '')),
        ]));
    }

    public function whatsappText(): string
    {
        $event = $this->allocation->event;
        $room = $this->roomName();
        $type = $this->roomTypeName();

        $lines = [
            "Olá, {$this->personName()}! Sua hospedagem no evento {$event->name} está definida:",
            'Local: ' . $this->fullAddress(),
            'Entrada: ' . DateUtil::formatDateToBr($event->start_date),
            'Saída: ' . DateUtil::formatDateToBr($event->end_date),
        ];

        if ($room !== '') {
            $lines[] = 'Quarto: ' . $room . ($type !== '' ? " ({$type})" : '');
        }

        if (!empty($event->contact_name) || !empty($event->contact_phone)) {
            $lines[] = 'Dúvidas: ' . trim("{$event->contact_name} {$event->contact_phone}");
        }

        return implode("\n", $lines);
    }

    /** Número de referência estável para o e-mail e para o .ics. */
    public function reservationNumber(): string
    {
        return 'EV' . $this->allocation->event_id . '-' . $this->allocation->id;
    }
}
