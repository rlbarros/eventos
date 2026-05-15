<?php

namespace App\Livewire\Forms\Event\Driver;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;
use App\Models\EventDriver;

class EventDriverForm extends GenericForm
{

    public int $event_id = 0;
    public string $name = '';
    public string $phone = '';
    public string $vehicle = '';
    public int $capacity = 0;


    public function fixedRules(): array
    {
        return [
            'event_id' => 'required|integer|exists:events,id',
            'name' => 'required|string',
            'phone' => 'required|string',
            'vehicle' => 'required|string'
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

        /** @var EventDriver */
        $eventDriver = $model;

        if (empty($eventDriver) || empty($eventDriver->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventDriver->id;
        $this->event_id = $eventDriver->event_id;
        $this->name = $eventDriver->name;
        $this->phone = $eventDriver->phone;
        $this->vehicle = $eventDriver->vehicle;
        $this->capacity = $eventDriver->capacity;
    }
}
