<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Fee\EventFeeForm;
use App\Models\EventFee;
use App\Traits\Forms\Event\Fee\WithEventFeeProperties;
use App\Utils\CurrencyUtil;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventFeeProperties;

    public EventFeeForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.fees.fee';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->fee = CurrencyUtil::formatCurrencyToDb($this->form->fee);
        // campo vazio = sem limite (a taxa vale para qualquer ocupação)
        $this->form->min_occupants = $this->form->min_occupants === '' ? null : $this->form->min_occupants;
        $this->form->max_occupants = $this->form->max_occupants === '' ? null : $this->form->max_occupants;
    }

    #[On('events.fees.fee-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventFee());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    public function categories(): array
    {
        return array_map(fn(\App\Enum\FeeCategoryEnum $category) => $category->value, \App\Enum\FeeCategoryEnum::cases());
    }

    #[On('events.fees.fee-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->injectBatch();
        $this->injectRoomType();
    }

    public function submitDisabledCondition(): bool
    {
        $emptyEventSiteRoomTypeId = empty($this->form->event_site_room_type_id);
        $emptyBatchId = empty($this->form->event_batch_id);
        $emptyCategory = empty($this->form->category);
        $emptyFee = empty($this->form->fee);

        return $emptyEventSiteRoomTypeId || $emptyBatchId || $emptyCategory || $emptyFee;
    }

    #[On('event-site-room-type-selected')]
    public function handleRoomTypeSelected(int $eventSiteRoomTypeId)
    {
        $this->form->event_site_room_type_id = $eventSiteRoomTypeId;
        $this->checkSubmitButtonDisabled();
    }

    public function injectRoomType()
    {
        $this->dispatch('event-site-room-type-injected', $this->form->event_site_room_type_id);
    }

    #[On('event-batch-selected')]
    public function handleBatchSelected(int $eventBatchId)
    {
        $this->form->event_batch_id = $eventBatchId;
        $this->checkSubmitButtonDisabled();
    }

    public function injectBatch()
    {
        $this->dispatch('event-batch-injected', $this->form->event_batch_id);
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <livewire:selects.room-types :readonly="$this->isReadonly()" :eventSiteId="$eventSiteId" :form="$form" class="space-x-2" />
    <livewire:selects.batches :readonly="$this->isReadonly()" :eventId="$eventId" :form="$form" class="space-x-2" />

    <flux:field class="w-full">
        <flux:label>Categoria</flux:label>

        <flux:select wire:model.live="form.category" wire:change="checkSubmitButtonDisabled" required>
            <flux:select.option>Selecione a categoria...</flux:select.option>
            @foreach ($this->categories() as $category)
            <flux:select.option :wire:key="$category" :value="$category">{{ $category }}</flux:select.option>
            @endforeach
        </flux:select>
    </flux:field>

    <div class="flex gap-4">
        <flux:field class="w-full">
            <flux:label>Pessoas no quarto (de)</flux:label>
            <flux:input type="number" min="1" placeholder="qualquer" wire:model="form.min_occupants" :readonly="$this->isReadonly()" />
            <flux:error name="form.min_occupants" />
        </flux:field>
        <flux:field class="w-full">
            <flux:label>Pessoas no quarto (até)</flux:label>
            <flux:input type="number" min="1" placeholder="qualquer" wire:model="form.max_occupants" :readonly="$this->isReadonly()" />
            <flux:error name="form.max_occupants" />
        </flux:field>
    </div>
    <flux:text size="sm">Deixe em branco para valer em qualquer ocupação. A taxa é sempre por pessoa; use uma taxa por categoria (Integral e Infantil) para cada faixa.</flux:text>

    <flux:field>
        <flux:label>Taxa (por pessoa)</flux:label>
        <flux:input placeholder="insira o valor" wire:model="form.fee" placeholder="0,00" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()">
            <x-slot name="prefix">
                R$
            </x-slot>
        </flux:input>
        <flux:error name="form.fee" />
    </flux:field>



</livewire:pages::forms.generic-form>