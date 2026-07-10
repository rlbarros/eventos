<?php

namespace App\Traits\Forms\Event\Fee;

use App\Models\EventFee;


trait WithEventFeeProperties
{
    public int $eventId = 0;
    public int $eventSiteId = 0;

    public bool $searchVisible = false;

    public function model()
    {
        return new EventFee();
    }

    public function routeName(): string
    {
        return 'event-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
            'selectedTab' => 'fees-tab'
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
