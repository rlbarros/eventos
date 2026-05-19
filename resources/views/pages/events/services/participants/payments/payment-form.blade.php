<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Participant\Service\EventParticipantServiceForm;
use App\Traits\Forms\Event\Service\Partipants\WithEventServiceParticipantsProperties;
use App\Utils\CurrencyUtil;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventServiceParticipantsProperties;

    public EventParticipantServiceForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.services.participants.participant';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->event_service_id = $this->serviceId;
        $this->form->person_id = $this->personId;
        $this->form->amount = CurrencyUtil::formatCurrencyToDb($this->form->amount);
    }

    #[On('events.services.participants.participant-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    #[On('events.services.participants.participant-view')]
    public function handleEventSiteViewRequest(int $id)
    {
        $this->form->formMode = FormModeEnum::View;
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
        $this->dispatchEventSiteRoomTypeInjected();
    }

    public function submitDisabledCondition(): bool
    {
        $emptyPersonId = empty($this->form->person_id);

        return $emptyPersonId;
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