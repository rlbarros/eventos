<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Service\EventServiceForm;
use App\Models\EventService;
use App\Traits\Forms\Event\Service\WithEventServiceProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventServiceProperties;

    public EventServiceForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.services.service';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->fee = str_replace(',', '.', $this->form->fee);
    }

    #[On('events.services.service-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventService());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    #[On('events.services.service-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    public function submitDisabledCondition(): bool
    {
        $emptyName = empty($this->form->name);
        $emptyFee = empty($this->form->fee);

        return $emptyName || $emptyFee;
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    <flux:field>
        <flux:label>Nome *</flux:label>
        <flux:input placeholder="insira o nome do serviço" wire:model="form.name" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.name" />
    </flux:field>

    <flux:field>
        <flux:label>Taxa</flux:label>
        <flux:input placeholder="insira o valor" wire:model="form.fee" placeholder="0,00" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()">
            <x-slot name="prefix">
                R$
            </x-slot>
        </flux:input>
        <flux:error name="form.fee" />
    </flux:field>


</livewire:pages::forms.generic-form>