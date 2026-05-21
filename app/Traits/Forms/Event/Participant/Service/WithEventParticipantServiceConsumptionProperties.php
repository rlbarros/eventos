<?php

namespace App\Traits\Forms\Event\Participant\Service;

use App\Models\EventServiceParticipantConsumption;

trait WithEventParticipantServiceConsumptionProperties
{
    public int $eventId = 0;

    public function model()
    {
        return new EventServiceParticipantConsumption();
    }

    public function routeName(): string
    {
        return 'dashboard';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
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
            ['event_id', '=', $this->eventId]
        ];
    }

    public function whereHasTable(): string
    {
        return 'person';
    }
}
