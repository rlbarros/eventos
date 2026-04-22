<?php

namespace App\Traits\Forms\Event\Participant;

use App\Models\EventParticipantAllocation;


trait WithEventParticipantProperties
{
    public $eventId;

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
}
