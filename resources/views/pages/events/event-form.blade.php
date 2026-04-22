<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\EventForm;
use App\Traits\Forms\Event\WithEventProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventProperties;

    public EventForm $form;

    public $dateConfig = ['altFormat' => 'd/m/Y'];

    public function form()
    {
        return $this->form;
    }

    public function submitDisabledCondition(): bool
    {

        $emptyName = empty($this->form->name);
        $emptyStartDate = empty($this->form->start_date);
        $emptyEndDate = empty($this->form->end_date);
        $emptyChurch = empty($this->form->church_id);
        $emptyEventSite = empty($this->form->event_site_id);
        return $emptyName || $emptyStartDate || $emptyEndDate || $emptyChurch || $emptyEventSite;
    }

    public function beforeSave(): void {}

    public function modalName(): string
    {
        return 'events.event';
    }

    #[On('events.event-create')]
    public function handleCreatingRequest()
    {
        $this->resetFormAndShowModal();
    }

    #[On('events.event-edit')]
    public function handleEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->dispatchChurchExternalySelected();
        $this->dispatchEventSiteExternalySelected();
    }

    #[On('events.event-view')]
    public function handleViewRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
        $this->dispatchStateCityExternalySelected();
    }

    #[On('church-internaly-selected')]
    public function handleChurchInternalySelected($churchId)
    {
        $this->form->church_id = $churchId;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchChurchExternalySelected()
    {
        $this->dispatch('church-externaly-selected', churchId: $this->form->church_id);
    }

    #[On('event-site-internaly-selected')]
    public function handleEventSiteInternalySelected($eventSiteId)
    {
        $this->form->event_site_id = $eventSiteId;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchEventSiteExternalySelected()
    {
        $this->dispatch('event-site-externaly-selected', eventSiteId: $this->form->event_site_id);
    }
};
?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <flux:field>
        <flux:label>Nome</flux:label>
        <flux:input placeholder="insira o nome do evento" wire:model="form.name" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.name" />
    </flux:field>


    <flux:field>
        <flux:label>Data de Início *</flux:label>
        <flux:input type="date" wire:model="form.start_date" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.start_date" />
    </flux:field>

    <flux:field>
        <flux:label>Data de Fim *</flux:label>
        <flux:input type="date" wire:model="form.end_date" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.end_date" />
    </flux:field>

    <livewire:autocompletes::churches :readonly="$this->isReadonly()" :form="$form" class="space-x-2" />
    <livewire:autocompletes::event-sites :readonly="$this->isReadonly()" :form="$form" class="space-x-2" />
</livewire:pages::forms.generic-form>