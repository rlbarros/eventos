<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Trip\EventTripForm;
use App\Models\EventTrip;
use App\Traits\Forms\Event\Trip\WithEventTripProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventTripProperties;

    public EventTripForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.trips.trip';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
    }

    #[On('event-driver-selected')]
    public function handleBatchSelected(int $eventDriverId)
    {
        $this->form->event_driver_id = $eventDriverId;
        $this->checkSubmitButtonDisabled();
    }

    public function injectDriver()
    {
        $this->dispatch('event-driver-injected', $this->form->event_driver_id);
    }

    #[On('events.trips.trip-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventTrip());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    #[On('events.trips.trip-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->injectDriver();
    }

    public function submitDisabledCondition(): bool
    {
        $emptyDriverId = empty($this->form->event_driver_id);
        $emptyFrom = empty($this->form->from);
        $emptyStartDate = empty($this->form->start_date);
        $emptyTo = empty($this->form->to);
        $emptyEndDate = empty($this->form->end_date);

        return $emptyDriverId || $emptyFrom || $emptyStartDate || $emptyTo || $emptyEndDate;
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <livewire:selects.drivers :readonly="$this->isReadonly()" :eventId="$eventId" :form="$form" class="space-x-2" />

    <flux:field>
        <flux:label>Origem</flux:label>
        <flux:input placeholder="insira o origem da viagem" wire:model="form.from" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.from" />
    </flux:field>

    <flux:field>
        <flux:label>Partida</flux:label>
        <flux:input type="date" wire:model="form.start_date" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.start_date" />
    </flux:field>

    <flux:field>
        <flux:label>Destino</flux:label>
        <flux:input placeholder="insira o destino da viagem" wire:model="form.to" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.to" />
    </flux:field>

    <flux:field>
        <flux:label>Chegada</flux:label>
        <flux:input type="date" wire:model="form.end_date" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.end_date" />
    </flux:field>


</livewire:pages::forms.generic-form>