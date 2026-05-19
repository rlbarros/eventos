<?php

namespace App\Traits\Forms\Event\Service\Partipants;

use App\Models\EventServiceParticipantConsumption;

trait WithEventServiceParticipantsProperties
{
    public int $eventId = 0;
    public int $serviceId = 0;
    public int $personId = 0;

    public function model()
    {
        return new EventServiceParticipantConsumption();
    }

    public function routeName(): string
    {
        return 'service-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
            'serviceId' => $this->serviceId
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
            ['event_id', '=', $this->eventId],
            ['event_service_id', '=', $this->serviceId]
        ];

        if (empty($this->personId)) {
            return $baseConditions;
        }

        array_push($baseConditions, ['person_id', '=', $this->personId]);
        return $baseConditions;
    }
}
