<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Church\ChurchForm;
use App\Models\AdministrationChurch;
use App\Traits\Forms\Church\WithChurchProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithChurchProperties;

    public ChurchForm $form;

    public function form()
    {
        return $this->form;
    }

    public function submitDisabledCondition(): bool
    {

        $emptyName = empty($this->form->name);
        $emptyState = empty($this->form->state_id);
        $emptyCity = empty($this->form->city_id);
        return $emptyName || $emptyState || $emptyCity;
    }

    public function beforeSave(): void
    {
        if ($this->form->administration_system_id === '') {
            $this->form->administration_system_id = null;
        }
    }

    /** Igrejas da administração para o vínculo (ativas, e a já vinculada mesmo se inativa). */
    public function administrationChurches()
    {
        return AdministrationChurch::query()
            ->where(fn ($q) => $q->where('active', true)->orWhere('id', $this->form->administration_system_id ?: 0))
            ->orderBy('superintendence_name')->orderBy('name')
            ->get();
    }

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

    #[On('state-city-selected')]
    public function handleStateCitySelected(int $stateId, int $cityId)
    {
        $this->form->state_id = $stateId;
        $this->form->city_id = $cityId;
    }

    public function dispatchStateCityExternalySelected()
    {
        $this->dispatch('state-city-externaly-selected', stateId: $this->form->state_id, cityId: $this->form->city_id);
    }
}

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <flux:field>
        <flux:label>Nome</flux:label>
        <flux:input placeholder="insira o nome do igreja" wire:model="form.name" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.name" />
    </flux:field>

    <livewire:autocompletes::states-cities :readonly="$this->isReadonly()" class="space-x-2" />

    <flux:field>
        <flux:label>Igreja na administração</flux:label>
        <flux:select wire:model="form.administration_system_id" :disabled="$this->isReadonly() || ! $this->form->canEditAdministrationLink()">
            <flux:select.option value="">Sem vínculo</flux:select.option>
            @foreach ($this->administrationChurches() as $administrationChurch)
                <flux:select.option :value="$administrationChurch->id">{{ $administrationChurch->label() }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:description>É por este vínculo que os anfitriões de igreja e de superintendência enxergam os eventos desta igreja.</flux:description>
        <flux:error name="form.administration_system_id" />
    </flux:field>
</livewire:pages::forms.generic-form>