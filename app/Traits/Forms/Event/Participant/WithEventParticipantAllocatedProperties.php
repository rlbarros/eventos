<?php

namespace App\Traits\Forms\Event\Participant;

use App\Models\EventParticipantAllocation;
use Illuminate\Support\Facades\DB;

trait WithEventParticipantAllocatedProperties
{
    public int $eventId = 0;

    public function model()
    {
        return new EventParticipantAllocation();
    }

    public function routeName(): string
    {
        return 'dashboard';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
            'selectedTab' => 'allocations-tab'
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
            ['event_id', '=', $this->eventId],
            [DB::raw("COALESCE(event_site_room_id, 0)"), '>', '0']
        ];
    }

    public function whereHasTable(): string
    {
        return 'person';
    }
}
