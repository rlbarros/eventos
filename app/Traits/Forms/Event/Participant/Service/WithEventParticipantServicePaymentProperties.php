<?php

namespace App\Traits\Forms\Event\Participant\Service;

use App\Models\EventServiceParticipantPayment;

trait WithEventParticipantServicePaymentProperties
{
    public int $eventId = 0;
    public int $personId = 0;
    public int $allocationId = 0;
    public int $consumptionId = 0;

    public function model()
    {
        return new EventServiceParticipantPayment();
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
            ['consumption_id', '=', $this->consumptionId]
        ];
    }

    public function whereHasTable(): string
    {
        return 'person';
    }
}
