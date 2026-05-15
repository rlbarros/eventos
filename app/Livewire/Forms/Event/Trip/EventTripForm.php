<?php

namespace App\Livewire\Forms\Event\Trip;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;
use App\Models\EventTrip;

class EventTripForm extends GenericForm
{

    public int $event_id = 0;
    public int $event_driver_id = 0;
    public string $from = '';
    public string $start_date = '';
    public string $to = '';
    public string $end_date = '';


    public function fixedRules(): array
    {
        return [
            'event_id' => 'required|integer|exists:events,id',
            'event_driver_id' => 'required|exists:events_drivers,id',
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

        /** @var EventTrip */
        $eventTrip = $model;

        if (empty($eventTrip) || empty($eventTrip->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventTrip->id;
        $this->event_id = $eventTrip->event_id;
        $this->event_driver_id = $eventTrip->event_driver_id;
        $this->from = $eventTrip->from;
        $this->start_date = $eventTrip->start_date;
        $this->to = $eventTrip->to;
        $this->end_date = $eventTrip->end_date;
    }
}
