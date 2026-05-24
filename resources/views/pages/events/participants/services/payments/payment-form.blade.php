<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Participant\Service\EventParticipantServicePaymentForm;
use App\Traits\Forms\Event\Participant\Service\WithEventParticipantServicePaymentProperties;
use App\Utils\CurrencyUtil;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventParticipantServicePaymentProperties;

    public EventParticipantServicePaymentForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.services.participants.payment';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->consumption_id = $this->consumptionId;
        $this->form->amount = CurrencyUtil::formatCurrencyToDb($this->form->amount);
    }

    #[On('events.services.participants.payment-create')]
    public function handleEventSiteCreateRequest()
    {
        $this->resetFormAndShowModal();
    }

    #[On('events.services.participants.payment-edit')]
    public function handleEventSiteEditRequest(int $id, int $consumptionId)
    {
        $this->consumptionId = $consumptionId;
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