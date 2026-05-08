<?php

namespace App\Traits\Forms\Event\Participant;

use App\Models\EventParticipantAllocation;


trait WithEventParticipantProperties
{
    public int $eventId = 0;
    public int $eventSiteId = 0;

    public function model()
    {
        return new EventParticipantAllocation();
    }

    public function routeName(): string
    {
        return 'event-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
            'selectedTab' => 'participants-tab'
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
