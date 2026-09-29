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
    public string $transporter_name = '';
    public string $transporter_phone = '';
    public string $from = '';
    public string $start_date = '';
    public string $start_time = '';
    public string $to = '';
    public string $end_date = '';
    public string $end_time = '';


    public function fixedRules(): array
    {
        return [
            'event_id' => 'required|integer|exists:events,id',
            'event_driver_id' => 'required|exists:events_drivers,id',
            'transporter_name' => 'nullable|string|max:200',
            'transporter_phone' => 'nullable|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
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

    public function additionalExcepts(): array
    {
        return ['start_time', 'end_time'];
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

        $start_date = date_create($eventTrip->start_date);
        $end_date = date_create($eventTrip->end_date);

        $this->id = $eventTrip->id;
        $this->event_id = $eventTrip->event_id;
        $this->event_driver_id = $eventTrip->event_driver_id;
        $this->transporter_name = $eventTrip->transporter_name ?? '';
        $this->transporter_phone = $eventTrip->transporter_phone ?? '';
        $this->from = $eventTrip->from;
        $this->start_date = date_format($start_date, 'Y-m-d');
        $this->start_time = date_format($start_date, 'H:i');
        $this->to = $eventTrip->to;
        $this->end_date = date_format($end_date, 'Y-m-d');
        $this->end_time = date_format($end_date, 'H:i');
    }
}
