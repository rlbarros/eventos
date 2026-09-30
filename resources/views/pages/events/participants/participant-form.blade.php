<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Participant\EventParticipantAllocationForm;
use App\Models\EventParticipantAllocation;
use App\Traits\Forms\Event\Participant\WithEventParticipantProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventParticipantProperties;

    public EventParticipantAllocationForm $form;

    public array $nonList;

    public function form()
    {
        return $this->form;
    }

    public function isPersonVisible(): bool
    {
        return $this->form->formMode === FormModeEnum::Create;
    }

    public function modalName(): string
    {
        return 'events.participants.participant';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->event_site_room_id = null;
        $this->form->payer_person_id = $this->validPayerId();
    }

    /**
     * Pagadores possíveis: participantes do evento que pagam por conta própria. Quem já paga por
     * outras pessoas não pode ter pagador, e ninguém escolhe a si mesmo.
     */
    public function payerOptions(): \Illuminate\Support\Collection
    {
        if ($this->hasDependents()) {
            return collect();
        }

        return EventParticipantAllocation::where('event_id', $this->eventId)
            ->whereNull('payer_person_id')
            ->where('person_id', '!=', (int) $this->form->person_id)
            ->with('person')
            ->get()
            ->sortBy(fn ($allocation) => $allocation->person->name);
    }

    private function hasDependents(): bool
    {
        return !empty($this->form->person_id)
            && EventParticipantAllocation::where('event_id', $this->eventId)
                ->where('payer_person_id', $this->form->person_id)
                ->exists();
    }

    private function validPayerId(): ?int
    {
        $payerId = (int) $this->form->payer_person_id;
        if (empty($payerId) || $payerId === (int) $this->form->person_id) {
            return null;
        }

        return $this->payerOptions()->contains('person_id', $payerId) ? $payerId : null;
    }

    #[On('events.participants.participant-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventParticipantAllocation());
        $this->form->payer_person_id = null;
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
        $this->dispatchEventSiteRoomTypeInjected();
    }

    #[On('events.participants.participant-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->dispatchPersonInjected();
        $this->dispatchEventSiteRoomTypeInjected();
    }

    #[On('events.participants.participant-view')]
    public function handleEventSiteViewRequest(int $id)
    {
        $this->form->formMode = FormModeEnum::View;
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
        $this->dispatchEventSiteRoomTypeInjected();
    }

    public function submitDisabledCondition(): bool
    {
        $emptyPersonId = empty($this->form->person_id);
        $emptyEvemtSiteRoomTypeId = empty($this->form->event_site_room_type_id);

        return $emptyPersonId || $emptyEvemtSiteRoomTypeId;
    }

    #[On('person-selected')]
    public function handlePersonSelected(int $personId)
    {
        $this->form->person_id = $personId;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchPersonInjected()
    {
        $this->dispatch('person-injected', personId: $this->form->person_id);
    }

    #[On('event-site-room-type-selected')]
    public function handleEventSiteRoomTypeSelected(int $eventSiteRoomTypeId)
    {
        $this->form->event_site_room_type_id = $eventSiteRoomTypeId;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchEventSiteRoomTypeInjected()
    {
        $this->dispatch('event-site-room-type-injected', $this->form->event_site_room_type_id);
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    @if($this->isPersonVisible())
    <livewire:autocompletes::persons :fieldName="'person_id'" :label="'Pessoa'" :readonly="$this->isReadonly()" :form="$form" :nonList="$this->nonList" class="space-x-2" />
    @endif
    <livewire:selects.room-types :readonly="$this->isReadonly()" :eventSiteId="$eventSiteId" :form="$form" class="space-x-2" />

    <flux:field>
        <flux:label>Pagador da reserva</flux:label>
        <flux:select wire:model="form.payer_person_id" :disabled="$this->isReadonly()">
            <flux:select.option value="">Ele mesmo paga a própria inscrição</flux:select.option>
            @foreach ($this->payerOptions() as $payerAllocation)
            <flux:select.option :wire:key="'payer-' . $payerAllocation->person_id" :value="$payerAllocation->person_id">{{ $payerAllocation->person->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:text size="sm">Quem divide o quarto escolhe quem paga a reserva. A taxa de cada um depende de quantas pessoas ficam no quarto, e o pagador responde pelo total.</flux:text>
        <flux:error name="form.payer_person_id" />
    </flux:field>
</livewire:pages::forms.generic-form>