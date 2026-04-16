<?php

namespace App\Traits\Forms\EventSite;

use App\Models\EventSiteRoom;

trait WithEventSiteRoomProperties
{
    public $eventSiteId;

    public function model()
    {
        return new EventSiteRoom();
    }

    public function routeName(): string
    {
        return 'event-site-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventSiteId' => $this->eventSiteId,
            'selectedTab' => 'rooms-tab'
        ];
    }

    public function generMale(): bool
    {
        return true;
    }
}
