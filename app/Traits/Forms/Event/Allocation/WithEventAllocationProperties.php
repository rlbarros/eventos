<?php

namespace App\Traits\Forms\Event\Participant;

use App\Models\EventParticipantAllocation;


trait WithEventAllocationProperties
{
    public int $eventId = 0;

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
            'selectedTab' => 'allocations-tab'
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
            ['event_id', '=', $this->eventId]
        ];
    }
}
