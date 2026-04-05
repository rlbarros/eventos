<?php

use App\Enum\FormModeEnum;
use App\Enum\RoomTypesEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\EventSite\EventSiteRoomTypeForm;
use App\Models\EventSiteRoomType;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    public $eventSiteId;

    public EventSiteRoomTypeForm $form;

    public EventSiteRoomType $model;

    public function form()
    {
        return $this->form;
    }

    public function model()
    {
        if (empty($this->model)) {
            $this->model = new EventSiteRoomType();
        }
        return $this->model;
    }

    public function modalName(): string
    {
        return 'forms.event-sites.room-type';
    }

    public function modelName(): string
    {
        return EventSiteRoomType::modelName();
    }

    public function successMessage(): string
    {
        return 'Tipo de quarto ' . $this->form->getModel()->descriptor() . ' salvo com sucesso';
    }

    public function beforeSave(): void
    {
        $this->form->event_site_id = $this->eventSiteId;
    }

    #[On('forms.event-sites.room-type-create')]
    public function handleEventSiteCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventSiteRoomType());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    #[On('forms.event-sites.room-type-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    #[On('forms.event-sites.room-type-view')]
    public function handleEventSiteViewRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
    }

    public function roomTypes(): array
    {
        return array_map(fn(RoomTypesEnum $roomType) => $roomType->value, RoomTypesEnum::cases());
    }


    public function updated($propertyName)
    {
        $this->form->validateOnly($propertyName);
        $this->checkSubmitButtonDisabled();
    }

    public function submitDisabledCondition(): bool
    {

        $emptyName = empty($this->form->name);
        $emptyTypeId = empty($this->form->type);
        $emptyBeds = empty($this->form->beds);

        // $this->dispatch('log-event', [
        //     'obj' => [`form` => $this->form, 'emptyName' => $emptyName, 'emptyTypeId' => $emptyTypeId, 'emptyBeds' => $emptyBeds],
        //     'level' => 'info',
        // ]);

        return $emptyName || $emptyTypeId || $emptyBeds;
    }

    public function routeName(): string
    {
        return 'event-site-detail';
    }

    public function routeParameters(): array
    {
        return ['eventSiteId' => $this->eventSiteId];
    }
};

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">
    <flux:field>
        <flux:label>Nome</flux:label>
        <flux:input placeholder="insira o nome do tipo de quarto" wire:model="form.name" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.name" />
    </flux:field>
    <div class="flex gap-4">
        <flux:field class="w-50">
            <flux:label>Tipo</flux:label>

            <flux:select wire:model.live="form.type" required :disabled="$this->isReadonly()">
                @foreach ($this->roomTypes() as $roomType)
                <flux:select.option :wire:key="$roomType" :value="$roomType">{{ $roomType }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:error name="form.type" />
        </flux:field>
        <flux:field class="w-50">
            <flux:label>Número de leitos</flux:label>
            <flux:input type="number" placeholder="insira o número de leitos" wire:model="form.beds"
                :readonly="$this->isReadonly()" />
            <flux:error name="form.beds" />
        </flux:field>
    </div>
</livewire:pages::forms.generic-form>