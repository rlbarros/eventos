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
    }

    #[On('events.participants.participant-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventParticipantAllocation());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
        $this->dispatchEventSiteRoomTypeInjected();
    }

    #[On('events.participants.participant-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->dispatchPersonExternalySelected();
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

        return $emptyPersonId;
    }

    #[On('person-selected')]
    public function handlePersonSelected(int $personId)
    {
        $this->form->person_id = $personId;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchPersonExternalySelected()
    {
        $this->dispatch('person-externaly-selected', personId: $this->form->person_id);
    }

    #[On('event-site-room-type-selected')]
    public function handleEventSiteRoomTypeSelected(int $eventSiteRoomTypeId)
    {
        $this->form->event_site_room_type_id = $eventSiteRoomTypeId;
    }

    public function dispatchEventSiteRoomTypeInjected()
    {
        $this->dispatch('event-site-room-type-injected', $this->form->event_site_room_type_id);
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    @if($this->isPersonVisible())
    <livewire:autocompletes::persons :fieldName="'person_id'" :label="'Pessoa'" :readonly="$this->isReadonly()" :form="$form" class="space-x-2" />
    @endif
    <livewire:selects.room-types :readonly="$this->isReadonly()" :eventSiteId="$eventSiteId" :form="$form" class="space-x-2" />
</livewire:pages::forms.generic-form>