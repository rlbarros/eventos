<?php

namespace App\Livewire\Forms\Event\Participant\Service;

use App\Models\EventServiceParticipantConsumption;
use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;

class EventParticipantServiceForm extends GenericForm
{

    public $event_id = 0;
    public $event_service_id = 0;
    public $person_id = 0;
    public $payment_date = '';
    public $amount = '';

    public function fixedRules(): array
    {
        return [

            'event_id' => 'required|integer|exists:events,id',
            'event_service_id' => 'required|integer|exists:events_services,id',
            'person_id' => 'required|integer|exists:persons,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
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

        /** @var EventServiceParticipantConsumption */
        $eventServiceParticipantComsumption = $model;

        if (empty($eventServiceParticipantComsumption) || empty($eventServiceParticipantComsumption->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventServiceParticipantComsumption->id;
        $this->person_id = $eventServiceParticipantComsumption->person_id;
        $this->event_id = $eventServiceParticipantComsumption->event_id;
        $this->event_service_id = $eventServiceParticipantComsumption->event_service_id;
        $this->payment_date = $eventServiceParticipantComsumption->payment_date;
        $this->amount = $eventServiceParticipantComsumption->amount;
    }
}
