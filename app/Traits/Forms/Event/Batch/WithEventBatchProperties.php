<?php

namespace App\Traits\Forms\Event\Batch;

use App\Models\EventBatch;


trait WithEventBatchProperties
{
    public int $eventId = 0;

    public bool $searchVisible = false;

    public function model()
    {
        return new EventBatch();
    }

    public function routeName(): string
    {
        return 'event-detail';
    }

    public function routeParameters(): array
    {
        return [
            'eventId' => $this->eventId,
            'selectedTab' => 'batches-tab'
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

    public function columnFilter(): string
    {
        return '';
    }
}
