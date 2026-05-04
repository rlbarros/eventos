<?php

namespace App\Traits\Forms\EventSite;

use App\Models\EventSiteRoomType;

trait WithEventSiteRoomTypeProperties
{
    public int $eventSiteId = 0;

    public function model()
    {
        return new EventSiteRoomType();
    }

    public function routeName(): string
    {
        return 'event-site-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventSiteId' => $this->eventSiteId,
            'selectedTab' => 'rooms-types-tab'
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
            ['event_site_id', '=', $this->eventSiteId]
        ];
    }
}
