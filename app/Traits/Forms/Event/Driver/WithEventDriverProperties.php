<?php

namespace App\Traits\Forms\Event\Driver;

use App\Models\EventDriver;


trait WithEventDriverProperties
{
    public int $eventId = 0;

    public function model()
    {
        return new EventDriver();
    }

    public function routeName(): string
    {
        return 'event-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
            'selectedTab' => 'drivers-tab'
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
}
