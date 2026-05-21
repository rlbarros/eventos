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

        $emptyChurch = empty($this->form->church_id);
        $emptyName = empty($this->form->name);
        $emptyBirthDate = empty($this->form->birth_date);
        $emptyFunction = empty($this->form->function);

        return $emptyChurch || $emptyName || $emptyBirthDate || $emptyFunction;
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
        $this->dispatchChurchInjected();
    }

    #[On('forms.persons.person-edit')]
    public function handleEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->dispatchChurchInjected();
    }

    #[On('forms.persons.person-view')]
    public function handleViewRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
        $this->dispatchChurchInjected();
    }

    #[On('church-selected')]
    public function handleChurchSelect(int $id)
    {
        $this->form->church_id = $id;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchChurchInjected()
    {
        $this->dispatch('church-injected');
    }
}

?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <livewire:autocompletes.churches :churchId="$this->form->church_id" :readonly="$this->isReadonly()" />

    <flux:field>
        <flux:label>CPF</flux:label>
        <flux:input placeholder="000.000.000-00" wire:model="form.cpf" mask="999.999.999-99" :readonly="$this->isReadonly()" />
        <flux:error name="form.cpf" />
    </flux:field>

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


</livewire:pages::forms.generic-form>