<?php

namespace App\Traits\Forms\Event\Participant\Payment;

use App\Models\EventParticipantPayment;

trait WithEventParticipantPaymentProperties
{
    public int $eventId = 0;
    public int $personId = 0;
    public int $allocationId = 0;
    public int $eventSiteRoomTypeId = 0;

    public function model()
    {
        return new EventParticipantPayment();
    }

    public function routeName(): string
    {
        return 'participant-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
            'allocationId' => $this->allocationId,
            'selectedTab' => 'payments-tab'
        ];
    }

    public function generMale(): bool
    {
        return true;
    }

    public function customOrderingColumn(): string
    {
        return '';
    }

    public function customWhereIndex(): array
    {
        return [
            ['event_id', '=', $this->eventId],
            ['person_id', '=', $this->personId]
        ];
    }

    public function whereHasTable(): string
    {
        return '';
    }
}
