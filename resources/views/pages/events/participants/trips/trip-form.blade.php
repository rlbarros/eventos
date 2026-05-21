<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Participant\Trip\EventTripParticipantForm;
use App\Models\EventTripParticipant;
use App\Traits\Forms\Event\Participant\Trip\WithEventParticipantTripProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventParticipantTripProperties;

    public EventTripParticipantForm $form;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.participants.trips.trip';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->person_id = $this->personId;
    }


    #[On('event-trip-selected')]
    public function handleTripSelected(int $id)
    {
        $this->form->event_trip_id = $id;
        $this->checkSubmitButtonDisabled();
    }

    public function injectTrip(): void
    {
        $this->dispatch('event-trip-injected', $this->form->event_trip_id);
    }

    #[On('events.participants.trips.trip-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventTripParticipant());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }


    public function submitDisabledCondition(): bool
    {

        $emptyEventTripId = empty($this->form->event_trip_id);

        return $emptyEventTripId;
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    <livewire:selects.trips :readonly="$this->isReadonly()" :eventId="$eventId" :form="$form" class="space-x-2" />
</livewire:pages::forms.generic-form>