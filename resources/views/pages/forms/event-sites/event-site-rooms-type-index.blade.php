<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\EventSite\WithEventSiteRoomTypeProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventSiteRoomTypeProperties;

    public function indexArray(): array
    {
        return [
            'header' => 'Tipos de Quartos',
            'subHeader' => 'cadastre os tipos de quartos disponíveis nos locais de evento.',
            'createButtonLabel' => 'Criar Tipo de Quarto',
            'createActionEventName' => 'forms.event-sites.event-site-room-type-create'
        ];
    }

    public function customOrderingColumn(): string
    {
        return 'id';
    }

    public function customWhereIndex(): array
    {
        return [
            ['event_site_id', '=', $this->eventSiteId]
        ];
    }

    #[On('forms.event-sites.event-site-room-type-delete-confirmed')]
    public function handleEventSiteRoomTypeDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::forms.event-sites.event-site-room-type-form :eventSiteId="$this->eventSiteId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Nome</flux:table.column>
            <flux:table.column sortable>Tipo</flux:table.column>
            <flux:table.column sortable>Nº de leitos</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $roomType)
            <flux:table.row :key="$roomType->id">
                <flux:table.cell>{{ $roomType->id }}</flux:table.cell>
                <flux:table.cell>{{ $roomType->name }}</flux:table.cell>
                <flux:table.cell>{{ $roomType->type }}</flux:table.cell>
                <flux:table.cell>{{ $roomType->beds }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button wire:click="$dispatch('forms.event-sites.event-site-room-type-view', { id: {{ $roomType->id }} })" icon="document-magnifying-glass" style="cursor: pointer;"
                            size="sm" />
                        <flux:button wire:click="$dispatch('forms.event-sites.event-site-room-type-edit', { id: {{ $roomType->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $roomType->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$roomType->descriptor()}}', callbackDeleteEvent: 'forms.event-sites.event-site-room-type-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>