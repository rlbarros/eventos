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

    public function form()
    {
        return $this->form;
    }

    public $eventId;

    public function modalName(): string
    {
        return 'events.participants.participant';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
    }

    #[On('events.participants.participant-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventParticipantAllocation());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    #[On('events.participants.particpant-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    #[On('events.participants.participant-view')]
    public function handleEventSiteViewRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
    }

    public function submitDisabledCondition(): bool
    {
        $emptyPersonId = empty($this->form->person_id);

        return $emptyPersonId;
    }

    #[On('person-internaly-selected')]
    public function handlePersonInternalySelected($personId)
    {
        $this->form->person_id = $personId;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchPersonExternalySelected()
    {
        $this->dispatch('person-externaly-selected', personId: $this->form->person_id);
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    <livewire:autocompletes::persons :fieldName="'person_id'" :label="'Pessoa'" :readonly="$this->isReadonly()" :form="$form" class="space-x-2" />
</livewire:pages::forms.generic-form>