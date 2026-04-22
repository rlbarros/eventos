<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\EventSite\EventSiteRoomForm;
use App\Models\EventSiteRoom;
use App\Traits\Forms\EventSite\WithEventSiteRoomProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventSiteRoomProperties;

    public $eventSiteId;

    public EventSiteRoomForm $form;

    public function form()
    {
        return $this->form;
    }

    public function submitDisabledCondition(): bool
    {

        $emptyName = empty($this->form->name);
        $emptyRoomType = empty($this->form->event_site_room_type_id);
        return $emptyName || $emptyRoomType;
    }

    public function beforeSave(): void
    {

        $this->form->event_site_id = $this->eventSiteId;
    }

    public function modalName(): string
    {
        return 'forms.event-sites.event-site-room';
    }

    #[On('room-type-internaly-selected')]
    public function handleStateCityInternalySelected($eventSiteRoomTypeId)
    {
        $this->form->event_site_room_type_id = $eventSiteRoomTypeId;
        $this->checkSubmitButtonDisabled();
    }

    #[On('forms.event-sites.event-site-room-create')]
    public function handleEventSiteCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventSiteRoom());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    #[On('forms.event-sites.event-site-room-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    #[On('forms.event-sites.event-site-room-view')]
    public function handleEventSiteViewRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
    }
};
?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <flux:field>
        <flux:label>Nome</flux:label>
        <flux:input placeholder="insira o nome do quarto" wire:model="form.name" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.name" />
    </flux:field>

    <livewire:autocompletes::room-types :readonly="$this->isReadonly()" :eventSiteId="$eventSiteId" :form="$form" class="space-x-2" />
</livewire:pages::forms.generic-form>