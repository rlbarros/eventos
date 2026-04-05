<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\EventSite\EventSiteRoomForm;
use App\Models\EventSiteRoom;
use App\Models\EventSiteRoomType;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    public $eventSiteId;
    public EventSiteRoomForm $form;
    public EventSiteRoom $model;

    public function form()
    {
        return $this->form;
    }

    public function model()
    {
        if (empty($this->model)) {
            $this->model = new EventSiteRoom();
        }
        return $this->model;
    }

    public function submitDisabledCondition(): bool
    {
        $this->dispatch('log-event', ['obj' => $this->form, 'level' => 'info']);

        $emptyName = empty($this->form->name);



        return $emptyName;
    }

    public function modelName(): string
    {
        return EventSiteRoom::modelName();
    }

    public function successMessage(): string
    {
        return 'Quarto ' . $this->form->getModel()->descriptor() . ' salvo com sucesso';
    }

    public function routeName(): string
    {
        return 'event-site-detail';
    }

    public function routeParameters(): array
    {
        return ['eventSiteId' => $this->eventSiteId];
    }

    public function beforeSave(): void
    {
        $this->form->event_site_id = $this->eventSiteId;
    }

    public function modalName(): string
    {
        return 'forms.event-sites.room';
    }

    public function roomTypes(): array
    {
        return EventSiteRoomType::where('event_site_id', $this->eventSiteId)->pluck('name', 'id')->toArray();
    }

    #[On('forms.event-sites.room-create')]
    public function handleEventSiteCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventSiteRoom());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    #[On('forms.event-sites.room-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    #[On('forms.event-sites.room-view')]
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

    <livewire:autocompletes::room-types :readonly="$this->isReadonly()" :eventSiteId="$this->eventSiteId" class="space-x-2" />

</livewire:pages::forms.generic-form>