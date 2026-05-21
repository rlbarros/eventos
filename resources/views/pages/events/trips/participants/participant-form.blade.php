<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Participant\Trip\EventTripParticipantForm;
use App\Models\EventTripParticipant;
use App\Traits\Forms\Event\Trip\Participants\WithEventTripParticipantsProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventTripParticipantsProperties;

    public EventTripParticipantForm $form;

    #[Override]
    public function modalName(): string
    {
        return 'events.trips.participants.participant';
    }

    public function form()
    {
        return $this->form;
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->event_trip_id = $this->tripId;
    }

    #[On('events.trips.participants.participant-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventTripParticipant());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    public function submitDisabledCondition(): bool
    {
        $emptyPersonId = empty($this->form->person_id);

        return $emptyPersonId;
    }

    #[On('person-selected')]
    public function handlePersonSelected(int $personId)
    {
        $this->form->person_id = $personId;
        $this->checkSubmitButtonDisabled();
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    <livewire:autocompletes::persons :fieldName="'person_id'" :label="'Pessoa'" :readonly="$this->isReadonly()" :form="$form" class="space-x-2" />
</livewire:pages::forms.generic-form>