<?php

namespace App\Livewire\Forms\Event\Participant;

use App\Models\EventParticipantAllocation;
use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;

class EventParticipantAllocationForm extends GenericForm
{

    public $person_id = 0;
    public $event_id = 0;
    public $event_site_room_type_id = 0;
    public $event_site_room_id = 0;
    public $payer_person_id = null;

    public function fixedRules(): array
    {
        return [

            'event_id' => 'required|integer|exists:events,id',
            'person_id' => 'required|integer|exists:persons,id',
            'event_site_room_type_id' => 'required|integer|exists:event_site_room_types,id',
            'payer_person_id' => 'nullable|integer|exists:persons,id',
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
        $this->event_site_room_type_id = $eventParticipantAllocation->event_site_room_type_id;
        $this->event_site_room_id = $eventParticipantAllocation->event_site_room_id;
        $this->payer_person_id = $eventParticipantAllocation->payer_person_id;
    }
}
