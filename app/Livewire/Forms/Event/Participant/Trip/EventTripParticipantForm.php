<?php

namespace App\Livewire\Forms\Event\Participant\Trip;

use App\Models\EventTripParticipant;
use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;

class EventTripParticipantForm extends GenericForm
{

    public $event_id = 0;
    public $event_trip_id = 0;
    public $person_id = 0;

    public function fixedRules(): array
    {
        return [
            'event_id' => 'required|integer|exists:events,id',
            'event_trip_id' => 'required|integer|exists:events_trips,id',
            'person_id' => 'required|integer|exists:persons,id',
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

        /** @var EventTripParticipant */
        $eventTripParticipant = $model;

        if (empty($eventTripParticipant) || empty($eventTripParticipant->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventTripParticipant->id;
        $this->event_id = $eventTripParticipant->event_id;
        $this->event_trip_id = $eventTripParticipant->event_trip_id;
        $this->person_id = $eventTripParticipant->person_id;
    }
}
