<?php

namespace App\Livewire\Forms\Event\Participant\Service;

use App\Models\EventServiceParticipantPayment;
use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;

class EventParticipantServicePaymentForm extends GenericForm
{

    public $event_id = 0;
    public $consumption_id = 0;
    public $payment_date = '';
    public $amount = '';

    public function fixedRules(): array
    {
        return [

            'event_id' => 'required|integer|exists:events,id',
            'consumption_id' => 'required|integer|exists:events_services_participants_consumption,id',
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

        /** @var EventServiceParticipantPayment */
        $eventServiceParticipantComsumption = $model;

        if (empty($eventServiceParticipantComsumption) || empty($eventServiceParticipantComsumption->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventServiceParticipantComsumption->id;
        $this->consumption_id = $eventServiceParticipantComsumption->consumption_id;
        $this->payment_date = $eventServiceParticipantComsumption->payment_date;
        $this->amount = $eventServiceParticipantComsumption->amount;
    }
}
