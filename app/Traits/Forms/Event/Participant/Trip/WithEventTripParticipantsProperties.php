<?php

namespace App\Traits\Forms\Event\Participant\Trip;

use App\Models\EventTripParticipant;

trait WithEventTripParticipantsProperties
{
    public int $eventId = 0;

    public function model()
    {
        return new EventTripParticipant();
    }

    public function routeName(): string
    {
        return 'dashboard';
    }

    public function routeParameters(): array
    {
        return [
            'selectedTab' => 'trips-tab'
        ];
    }

    public function generMale(): bool
    {
        return false;
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
