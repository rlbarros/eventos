<?php

namespace App\Traits\Forms\Event\Participant\Service;

use App\Models\EventServiceParticipantConsumption;

trait WithEventParticipantServiceProperties
{
    public int $eventId = 0;
    public int $personId = 0;
    public int $allocationId = 0;

    public function model()
    {
        return new EventServiceParticipantConsumption();
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
            'selectedTab' => 'services-tab'
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
            ['event_id', '=', $this->eventId],
            ['person_id', '=', $this->personId]
        ];
    }
}
