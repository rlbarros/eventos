<?php

namespace App\Livewire\Forms\Event\Participant\Payment;

use App\Models\EventParticipantPayment;
use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;

class EventParticipantPaymentForm extends GenericForm
{

    public $event_id = 0;
    public $event_fee_id = 0;
    public $person_id = 0;
    public $payment_date = '';
    public $amount = '';

    public function fixedRules(): array
    {
        return [

            'event_id' => 'required|integer|exists:events,id',
            'event_fee_id' => 'required|integer|exists:events_fees,id',
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

        /** @var EventParticipantPayment */
        $eventParticipantPayment = $model;

        if (empty($eventParticipantPayment) || empty($eventParticipantPayment->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventParticipantPayment->id;
        $this->person_id = $eventParticipantPayment->person_id;
        $this->event_id = $eventParticipantPayment->event_id;
        $this->event_fee_id = $eventParticipantPayment->event_fee_id;
        $this->payment_date = $eventParticipantPayment->payment_date;
        $this->amount = $eventParticipantPayment->amount;
    }
}
