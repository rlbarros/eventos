<?php

namespace App\Livewire\Forms\Event\Batch;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;
use App\Models\EventBatch;

class EventBatchForm extends GenericForm
{

    public int $event_id = 0;
    public int $batch = 0;
    public string $start_date = '';
    public string $end_date = '';


    public function fixedRules(): array
    {
        return [
            'event_id' => 'required|integer|exists:events,id',
            'batch' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ];
    }

    public function insertRules(): array
    {
        return [];
    }

    public function updateRules(): array
    {
        return [];
    }

    public function setModel(FormModeEnum $formMode, GenericModel $model): void
    {
        $this->formMode = $formMode;
        $this->model = $model;

        /** @var EventBatch */
        $eventBatch = $model;

        if (empty($eventBatch) || empty($eventBatch->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventBatch->id;
        $this->event_id = $eventBatch->event_id;
        $this->batch = $eventBatch->batch;
        $this->start_date = $eventBatch->start_date;
        $this->end_date = $eventBatch->end_date;
    }
}
