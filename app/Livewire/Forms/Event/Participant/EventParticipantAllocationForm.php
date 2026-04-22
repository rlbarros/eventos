<?php

namespace App\Livewire\Forms\Event\Participant;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;

class EventParticipantAllocationForm extends GenericForm
{

    public $person_id = 0;
    public $event_id = 0;
    public $event_site_room_id = 0;

    public function fixedRules(): array
    {
        return [

            'event_id' => 'required|integer|exists:events,id',
            'person_id' => 'required|integer|exists:persons,id',
        ];
    }

    public function insertRules(): array
    {
        return [];
    }

    public function updateRules(): array
    {
        return [
            'event_site_room_id' => 'nullable|integer|exists:event_site_rooms,id'
        ];
    }

    public function setModel(FormModeEnum $formMode, GenericModel $model): void
    {
        $this->formMode = $formMode;
        $this->model = $model;

        /** @var EventParticipantAllocation */
        $eventParticipantAllocation = $model;

        if (empty($eventParticipantAllocation) || empty($eventParticipantAllocation->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventParticipantAllocation->id;
        $this->person_id = $eventParticipantAllocation->person_id;
        $this->event_id = $eventParticipantAllocation->event_id;
        $this->event_site_room_id = $eventParticipantAllocation->event_site_room_id;
    }
}
