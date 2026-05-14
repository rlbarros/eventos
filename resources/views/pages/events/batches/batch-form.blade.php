<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Batch\EventBatchForm;
use App\Models\EventBatch;
use App\Models\EventParticipantAllocation;
use App\Traits\Forms\Event\Batch\WithEventBatchProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventBatchProperties;

    public EventBatchForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.batches.batch';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
    }

    #[On('events.batches.batch-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventBatch());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    #[On('events.batches.batch-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    public function submitDisabledCondition(): bool
    {
        $emptyBatchId = empty($this->form->batch);
        $emptyStartDate = empty($this->form->start_date);
        $emptyEndDate = empty($this->form->end_date);

        return $emptyBatchId || $emptyStartDate || $emptyEndDate;
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    <flux:field>
        <flux:label>Lote *</flux:label>
        <flux:input placeholder="insira o número do lote" wire:model="form.batch" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.batch" />
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


</livewire:pages::forms.generic-form>