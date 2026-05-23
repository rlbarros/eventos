<?php

namespace App\Traits\Forms\Event\Service\Partipants;

use App\Models\EventServiceParticipantPayment;

trait WithEventServiceParticipantsPaymentsProperties
{
    public int $eventId = 0;
    public int $serviceId = 0;
    public int $consumptionId = 0;

    public function model()
    {
        return new EventServiceParticipantPayment();
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
        return [
            ['consumption_id', '=', $this->consumptionId]
        ];
    }
}
