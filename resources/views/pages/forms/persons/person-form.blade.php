<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Person\PersonForm;
use App\Traits\Forms\Person\WithPersonProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithPersonProperties;

    public PersonForm $form;

    public function form()
    {
        return $this->form;
    }

    public function submitDisabledCondition(): bool
    {

        $emptyName = empty($this->form->name);
        $emptyBirthDate = empty($this->form->birth_date);
        $emptyFunction = empty($this->form->function);
        $emptyChurch = empty($this->form->church_id);
        return $emptyName || $emptyBirthDate || $emptyFunction || $emptyChurch;
    }

    public function beforeSave(): void {}

    public function modalName(): string
    {
        return 'forms.persons.person';
    }

    #[On('forms.persons.person-create')]
    public function handleCreatingRequest()
    {
        $this->resetFormAndShowModal();
    }

    #[On('forms.persons.person-edit')]
    public function handleEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->dispatchChurchExternalySelected();
    }

    #[On('forms.persons.person-view')]
    public function handleViewRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
        $this->dispatchChurchExternalySelected();
    }

    #[On('church-internaly-selected')]
    public function handleStateCityInternalySelected($churchId)
    {
        $this->form->church_id = $churchId;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchChurchExternalySelected()
    {
        $this->dispatch('church-externaly-selected', churchId: $this->form->church_id);
    }
};
?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <livewire:autocompletes::churches :readonly="$this->isReadonly()" :form="$form" class="space-x-2" />

    <flux:field>
        <flux:label>Função *</flux:label>
        <flux:select wire:model="form.function" :disabled="$this->isReadonly()">
            <flux:select.option value="">Selecionar...</flux:select.option>
            <flux:select.option value="Membro">Membro</flux:select.option>
            <flux:select.option value="Pastor">Pastor</flux:select.option>
            <flux:select.option value="Convidado">Convidado</flux:select.option>
            <flux:select.option value="Obreiro">Obreiro</flux:select.option>
            <flux:select.option value="Diácono">Diácono</flux:select.option>
            <flux:select.option value="Pregador de Conferência">Pregador de Conferência</flux:select.option>
            <flux:select.option value="Presbítero">Presbítero</flux:select.option>
            <flux:select.option value="Evangelista">Evangelista</flux:select.option>
            <flux:select.option value="Bispo">Bispo</flux:select.option>
        </flux:select>
        <flux:error name="form.function" />
    </flux:field>

    <flux:field>
        <flux:label>Nome *</flux:label>
        <flux:input placeholder="insira o nome do pessoa" wire:model="form.name" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.name" />
    </flux:field>

    <flux:field>
        <flux:label>Data de Nascimento *</flux:label>
        <flux:input type="date" wire:model="form.birth_date" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.birth_date" />
    </flux:field>

    <flux:field>
        <flux:label>Telefone</flux:label>
        <flux:input placeholder="(00) 00000-0000" wire:model="form.phone" mask="(99) 99999-9999" :readonly="$this->isReadonly()" />
        <flux:error name="form.phone" />
    </flux:field>

    <livewire:autocompletes::persons :readonly="$this->isReadonly()" fieldName="father_id" label="Pai" :form="$form" class="space-x-2" />

    <livewire:autocompletes::persons :readonly="$this->isReadonly()" fieldName="mother_id" label="Mãe" :form="$form" class="space-x-2" />

    <livewire:autocompletes::persons :readonly="$this->isReadonly()" fieldName="spouse_id" label="Cônjuge" :form="$form" class="space-x-2" />



</livewire:pages::forms.generic-form>