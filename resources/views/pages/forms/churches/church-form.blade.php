<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Church\ChurchForm;
use App\Models\Church;
use App\Traits\Forms\Church\WithChurchProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithChurchProperties;

    public $churchId;
    public ChurchForm $form;
    public Church $model;

    public function form()
    {
        return $this->form;
    }

    public function submitDisabledCondition(): bool
    {

        $emptyName = empty($this->form->name);
        return $emptyName;
    }

    public function beforeSave(): void {}

    public function modalName(): string
    {
        return 'forms.churchs.church';
    }

    #[On('forms.churchs.church-create')]
    public function handleCreatingRequest()
    {
        $this->resetFormAndShowModal();
    }

    #[On('forms.churchs.church-edit')]
    public function handleEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->dispatchStateCityExternalySelected();
    }

    #[On('forms.churchs.church-view')]
    public function handleViewRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
        $this->dispatchStateCityExternalySelected();
    }

    #[On('state-city-internaly-selected')]
    public function handleStateCityInternalySelected($stateId, $cityId)
    {
        $this->form->state_id = $stateId;
        $this->form->city_id = $cityId;
    }

    public function dispatchStateCityExternalySelected()
    {
        $this->dispatch('state-city-externaly-selected', stateId: $this->form->state_id, cityId: $this->form->city_id);
    }
};
?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <flux:field>
        <flux:label>Nome</flux:label>
        <flux:input placeholder="insira o nome do igreja" wire:model="form.name" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.name" />
    </flux:field>

    <livewire:autocompletes::states-cities :readonly="$this->isReadonly()" class="space-x-2" />
</livewire:pages::forms.generic-form>