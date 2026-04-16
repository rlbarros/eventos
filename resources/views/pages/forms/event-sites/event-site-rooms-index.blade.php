<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\EventSite\WithEventSiteRoomProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventSiteRoomProperties;

    public function indexArray(): array
    {
        return [
            'header' => 'Quartos',
            'subHeader' => 'cadastre os tipos de quartos disponíveis nos locais de evento.',
            'createButtonLabel' => 'Criar Quarto',
            'createActionEventName' => 'forms.event-sites.event-site-room-create',
            'callbackDeleteEvent' => 'forms.event-sites.event-site-room-delete-confirmed',
        ];
    }

    #[On('forms.event-sites.event-site-room-delete-confirmed')]
    public function handleEventSiteRoomTypeDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }

    public function customWhereIndex(): array
    {
        return [
            ['event_site_id', '=', $this->eventSiteId]
        ];
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::forms.event-sites.event-site-room-form :eventSiteId="$this->eventSiteId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Nome</flux:table.column>
            <flux:table.column sortable>Tipo</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>


        <flux:table.rows>
            @foreach ($this->index() as $room)
            <flux:table.row :key="$room->id">
                <flux:table.cell>{{ $room->id }}</flux:table.cell>
                <flux:table.cell>{{ $room->name }}</flux:table.cell>
                <flux:table.cell>{{ $room->type() }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button wire:click="$dispatch('forms.event-sites.event-site-room-view', { id: {{ $room->id }} })" icon="document-magnifying-glass" style="cursor: pointer;"
                            size="sm" />
                        <flux:button wire:click="$dispatch('forms.event-sites.event-site-room-edit', { id: {{ $room->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $room->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$room->descriptor()}}', callbackEvent: 'forms.event-sites.event-site-room-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>