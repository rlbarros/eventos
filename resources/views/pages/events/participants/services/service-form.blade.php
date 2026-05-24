<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Participant\Service\EventParticipantServiceForm;
use App\Models\EventServiceParticipantConsumption;
use App\Traits\Forms\Event\Participant\Service\WithEventParticipantServiceProperties;
use App\Utils\CurrencyUtil;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventParticipantServiceProperties;

    public EventParticipantServiceForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.participants.services.service';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->person_id = $this->personId;
        $this->form->quantity = CurrencyUtil::formatCurrencyToDb($this->form->quantity);
    }

    #[On('event-service-selected')]
    public function handleServiceSelected(int $id)
    {
        $this->form->event_service_id = $id;
        $this->checkSubmitButtonDisabled();
    }

    public function injectService(): void
    {
        $this->dispatch('event-service-injected', $this->form->event_service_id);
    }

    #[On('events.participants.services.service-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventServiceParticipantConsumption());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }



    #[On('events.participants.services.service-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->injectService();
    }

    public function submitDisabledCondition(): bool
    {

        $emptyEventServiceId = empty($this->form->event_service_id);
        $emptyQuantity = empty($this->form->quantity);

        return $emptyEventServiceId || $emptyQuantity;
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    <livewire:selects.services :readonly="$this->isReadonly()" :eventId="$eventId" :form="$form" class="space-x-2" />
    <flux:field>
        <flux:label>quantidade</flux:label>
        <flux:input type="number" placeholder="insira a quantidade" wire:model.live="form.quantity" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.quantity" />
    </flux:field>
</livewire:pages::forms.generic-form>