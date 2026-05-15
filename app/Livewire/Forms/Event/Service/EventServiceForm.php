<?php

namespace App\Livewire\Forms\Event\Service;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;
use App\Models\EventService;

class EventServiceForm extends GenericForm
{

    public int $event_id = 0;
    public string $name = '';
    public string $fee = '';


    public function fixedRules(): array
    {
        return [
            'event_id' => 'required|integer|exists:events,id',
            'name' => 'required|string',
            'fee' => 'required|numeric|min:0.01|max:999999.99'
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

        /** @var EventService */
        $eventService = $model;

        if (empty($eventService) || empty($eventService->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventService->id;
        $this->event_id = $eventService->event_id;
        $this->name = $eventService->name;
        $this->fee = $eventService->fee;
    }
}
