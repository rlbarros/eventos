<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Participant\Payment\EventParticipantPaymentForm;
use App\Models\EventFee;
use App\Models\EventParticipantPayment;
use App\Traits\Forms\Event\Participant\Payment\WithEventParticipantPaymentProperties;
use App\Utils\CurrencyUtil;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventParticipantPaymentProperties;

    public EventParticipantPaymentForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.participants.payments.payment';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->person_id = $this->personId;
        $paymentDate = $this->form->payment_date;
        $eventFee = EventFee::where('event_id', $this->eventId)
            ->where('event_site_room_type_id', $this->eventSiteRoomTypeId)
            ->with('event_batch')
            ->whereHas('event_batch', function ($query) use ($paymentDate) {
                $query->where('start_date', '<=', $paymentDate)
                    ->where('end_date', '>=', $paymentDate);
            })->first();
        $this->form->event_fee_id = $eventFee->id;
        $this->form->amount = CurrencyUtil::formatCurrencyToDb($this->form->amount);
        $this->form->payment_date = $paymentDate;
    }

    #[On('events.participants.payments.payment-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventParticipantPayment());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }



    #[On('events.participants.payments.payment-edit')]
    public function handleEventSiteEditRequest(int $id, int $personId)
    {
        $this->personId = $personId;
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    public function submitDisabledCondition(): bool
    {
        $emptyPaymentDate = empty($this->form->payment_date);

        $emptyAmount = empty($this->form->amount);

        return $emptyPaymentDate || $emptyAmount;
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <flux:field>
        <flux:label>Data de Pagamento *</flux:label>
        <flux:input type="date" wire:model="form.payment_date" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.payment_date" />
    </flux:field>

    <flux:field>
        <flux:label>Valor Pago</flux:label>
        <flux:input placeholder="insira o valor" wire:model.live="form.amount" placeholder="0,00" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()">
            <x-slot name="prefix">
                R$
            </x-slot>
        </flux:input>
        <flux:error name="form.amount" />
    </flux:field>



</livewire:pages::forms.generic-form>