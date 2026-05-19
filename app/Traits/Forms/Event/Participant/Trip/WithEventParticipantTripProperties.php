<?php

namespace App\Traits\Forms\Event\Participant\Trip;

use App\Models\EventTripParticipant;

trait WithEventParticipantTripProperties
{
    public int $eventId = 0;
    public int $personId = 0;
    public int $allocationId = 0;

    public function model()
    {
        return new EventTripParticipant();
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
            'selectedTab' => 'trips-tab'
        ];
    }

    public function generMale(): bool
    {
        return true;
    }

    public function customOrderingColumn(): string
    {
        return 'id';
    }

    public function customWhereIndex(): array
    {
        return [
            ['person_id', '=', $this->personId]
        ];
    }
}
