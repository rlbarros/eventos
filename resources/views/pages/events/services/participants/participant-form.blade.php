<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\Participant\Service\EventParticipantServiceForm;
use App\Models\EventServiceParticipantConsumption;
use App\Traits\Forms\Event\Service\Partipants\WithEventServiceParticipantsProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventServiceParticipantsProperties;

    public EventParticipantServiceForm $form;

    public array $nonList;

    public function form()
    {
        return $this->form;
    }

    public function modalName(): string
    {
        return 'events.services.participants.participant';
    }

    public function beforeSave(): void
    {
        $this->form->event_id = $this->eventId;
        $this->form->event_service_id = $this->serviceId;
    }

    #[On('events.services.participants.participant-create')]
    public function handleParticipantCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventServiceParticipantConsumption());
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
    <livewire:autocompletes::persons :fieldName="'person_id'" :label="'Pessoa'" :readonly="$this->isReadonly()" :form="$form" :nonList="$nonList" class="space-x-2" />

    <flux:field>
        <flux:label>quantidade</flux:label>
        <flux:input type="number" placeholder="insira a quantidade" wire:model.live="form.quantity" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.quantity" />
    </flux:field>
</livewire:pages::forms.generic-form>