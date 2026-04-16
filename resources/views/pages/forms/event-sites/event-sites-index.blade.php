<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\EventSite\WithEventSiteProperties;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;


new #[Title('Locais de Evento')] class extends GenericIndexComponent
{
    use WithEventSiteProperties;

    public function indexArray(): array
    {
        return [
            'header' => 'Locais de Evento',
            'subHeader' => 'cadastre as chácaras, estâncias ou quaisquer outros locais de recepção onde ocorrem os eventos da IEA.',
            'createButtonLabel' => 'Criar Local de Evento',
            'createActionEventName' => 'forms.event-sites.event-site-create',
            'callbackDeleteEvent' => 'forms.event-sites.event-site-delete-confirmed',
        ];
    }

    #[On('forms.event-sites.event-site-delete-confirmed')]
    public function handleEventSiteDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-index :indexArray="$this->indexArray()">
    <livewire:pages::forms.event-sites.event-site-form />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Nome</flux:table.column>
            <flux:table.column sortable>Telefone</flux:table.column>
            <flux:table.column sortable>Endereço</flux:table.column>
            <flux:table.column sortable>Cidade</flux:table.column>
            <flux:table.column sortable>Estado</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $eventSite)
            <flux:table.row :key="$eventSite->id">
                <flux:table.cell>{{ $eventSite->id }}</flux:table.cell>
                <flux:table.cell>{{ $eventSite->name }}</flux:table.cell>
                <flux:table.cell>{{ $eventSite->phone }}</flux:table.cell>
                <flux:table.cell>{{ $eventSite->address }}</flux:table.cell>
                <flux:table.cell>{{ $eventSite->city->name }}</flux:table.cell>
                <flux:table.cell>{{ $eventSite->state->name }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button href="event-site-detail/{{ $eventSite->id }}?selectedTab=rooms-types-tab" icon="document-text" style="cursor: pointer;" wire:navigate
                            size="sm" />
                        <flux:button wire:click="$dispatch('forms.event-sites.event-site-view', { id: {{ $eventSite->id }} })" icon="document-magnifying-glass" style="cursor: pointer;"
                            size="sm" />
                        <flux:button wire:click="$dispatch('forms.event-sites.event-site-edit', { id: {{ $eventSite->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $eventSite->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$eventSite->descriptor()}}', callbackDeleteEvent: 'forms.event-sites.event-site-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-index>