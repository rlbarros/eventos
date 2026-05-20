<?php

namespace App\Traits\Forms\Event\Trip\Participants;

use App\Models\EventTripParticipant;

trait WithEventTripParticipantsProperties
{
    public int $eventId = 0;
    public int $tripId = 0;
    public int $personId = 0;

    public function model()
    {
        return new EventTripParticipant();
    }

    public function routeName(): string
    {
        return 'trip-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
            'tripId' => $this->tripId
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
        $baseConditions = [
            ['event_trip_id', '=', $this->tripId]
        ];

        if (empty($this->personId)) {
            return $baseConditions;
        }

        array_push($baseConditions, ['person_id', '=', $this->personId]);
        return $baseConditions;
    }

    public function whereHasTable(): string
    {
        return 'person';
    }
}
