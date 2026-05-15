<?php

namespace App\Traits\Forms\Event\Trip;

use App\Models\EventTrip;


trait WithEventTripProperties
{
    public int $eventId = 0;

    public function model()
    {
        return new EventTrip();
    }

    public function routeName(): string
    {
        return 'event-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
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

    public function columnFilter(): string
    {
        return '';
    }
}
