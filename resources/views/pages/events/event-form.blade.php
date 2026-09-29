<?php

use App\Enum\FormModeEnum;
use App\Livewire\Components\GenericFormComponent;
use App\Livewire\Forms\Event\EventForm;
use App\Traits\Forms\Event\WithEventProperties;
use Livewire\Attributes\On;

new class extends GenericFormComponent {

    use WithEventProperties;

    public EventForm $form;

    public array $dateConfig = ['altFormat' => 'd/m/Y'];

    public function form()
    {
        return $this->form;
    }

    public function submitDisabledCondition(): bool
    {
        $emptyName = empty($this->form->name);
        $emptyStartDate = empty($this->form->start_date);
        $emptyEndDate = empty($this->form->end_date);
        $emptyChurch = empty($this->form->church_id);
        $emptyEventSite = empty($this->form->event_site_id);
        return $emptyName || $emptyStartDate || $emptyEndDate || $emptyChurch || $emptyEventSite;
    }

    public function beforeSave(): void {}

    /**
     * Abrangências que o usuário pode escolher (anfitrião: as da jurisdição). Na visualização e na
     * edição a do próprio evento sempre aparece, para o campo não ficar em branco.
     */
    public function scopeOptions(): array
    {
        $labels = ['nacional' => 'Nacional', 'superintendencia' => 'Superintendência', 'igreja' => 'Igreja'];
        $allowed = auth()->user()?->hostJurisdiction()->allowedScopes() ?? [];
        if (! empty($this->form->scope)) {
            $allowed[] = $this->form->scope;
        }

        return array_intersect_key($labels, array_flip($allowed));
    }

    public function modalName(): string
    {
        return 'events.event';
    }

    #[On('events.event-create')]
    public function handleCreatingRequest()
    {
        $this->resetFormAndShowModal();
    }

    #[On('events.event-edit')]
    public function handleEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
        $this->dispatchChurchExternalySelected();
        $this->dispatchEventSiteExternalySelected();
    }

    #[On('events.event-view')]
    public function handleViewRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
        $this->dispatchChurchExternalySelected();
        $this->dispatchEventSiteExternalySelected();
    }

    #[On('church-selected')]
    public function handleChurchSelected(int $churchId)
    {
        $this->form->church_id = $churchId;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchChurchExternalySelected()
    {
        $this->dispatch('church-injected', churchId: $this->form->church_id);
    }

    #[On('event-site-selected')]
    public function handleEventSiteSelected(int $eventSiteId)
    {
        $this->form->event_site_id = $eventSiteId;
        $this->checkSubmitButtonDisabled();
    }

    public function dispatchEventSiteExternalySelected()
    {
        $this->dispatch('event-site-injected', eventSiteId: $this->form->event_site_id);
    }
};
?>

<livewire:pages::forms.generic-form :modalArray="$this->modalArray()" :submitDisabled="$this->submitDisabled">

    <flux:field>
        <flux:label>Nome</flux:label>
        <flux:input placeholder="insira o nome do evento" wire:model="form.name" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.name" />
    </flux:field>


    <flux:field>
        <flux:label>Contato principal (quem recebe as inscrições)</flux:label>
        <flux:input placeholder="nome do responsável pelas inscrições" wire:model="form.contact_name" :readonly="$this->isReadonly()" />
        <flux:error name="form.contact_name" />
    </flux:field>

    <flux:field>
        <flux:label>Telefone do contato principal</flux:label>
        <flux:input placeholder="(00) 00000-0000" mask="(99) 99999-9999" wire:model="form.contact_phone" :readonly="$this->isReadonly()" />
        <flux:error name="form.contact_phone" />
    </flux:field>

    <flux:field>
        <flux:label>Data de Início *</flux:label>
        <flux:input type="date" wire:model="form.start_date" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.start_date" />
    </flux:field>

    <flux:field>
        <flux:label>Data de Fim *</flux:label>
        <flux:input type="date" wire:model="form.end_date" wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
        <flux:error name="form.end_date" />
    </flux:field>

    <flux:field>
        <flux:label>Abrangência *</flux:label>
        <flux:select wire:model="form.scope" :disabled="$this->isReadonly()">
            @foreach ($this->scopeOptions() as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:error name="form.scope" />
    </flux:field>

    <flux:field>
        <flux:label>Idade Infantil Máxima</flux:label>
        <flux:input type="number" placeholder="insira a idade infantil máxima" wire:model="form.children_age" :readonly="$this->isReadonly()" />
        <flux:error name="form.children_age" />
    </flux:field>

    <livewire:autocompletes::churches :readonly="$this->isReadonly()" :form="$form" class="space-x-2" />
    <livewire:autocompletes::event-sites :readonly="$this->isReadonly()" :form="$form" class="space-x-2" />
</livewire:pages::forms.generic-form>