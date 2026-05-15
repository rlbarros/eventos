<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Driver\EventDriverForm;
use App\Models\EventDriver;
use App\Traits\Forms\Event\Driver\WithEventDriverProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventDriverProperties;

    public EventDriverForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.drivers.driver';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
    }

    #[On('events.drivers.driver-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventDriver());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    #[On('events.drivers.driver-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    public function submitDisabledCondition(): bool
    {
        $emptyName = empty($this->form->name);
        $emptyPhone = empty($this->form->phone);
        $emptyVehicle = empty($this->form->vehicle);
        $emptyCapacity = empty($this->form->capacity);

        return $emptyName || $emptyPhone || $emptyVehicle || $emptyCapacity;
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    <flux:field>
        <flux:label>Nome</flux:label>
        <flux:input placeholder="insira o nome do motorista" wire:model="form.name" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.name" />
    </flux:field>

    <flux:field>
        <flux:label>Telefone</flux:label>
        <flux:input placeholder=" {{$this->placeHolder('(00) 00000-0000')}}" mask="(99) 99999-9999"
            wire:model="form.phone" :readonly="$this->isReadonly()" />
        <flux:error name="form.phone" />
    </flux:field>

    <flux:field>
        <flux:label>Veículo</flux:label>
        <flux:input placeholder="insira marca e modelo do veículo" wire:model="form.vehicle" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.vehicle" />
    </flux:field>

    <flux:field>
        <flux:label>Capacidade</flux:label>
        <flux:input type="number" wire:model="form.capacity" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.capacity" />
    </flux:field>

</livewire:pages::forms.generic-form>