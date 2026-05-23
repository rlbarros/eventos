<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Trip\WithEventTripProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventTripProperties;


    public function indexArray(): array
    {
        return [
            'header' => 'Viagens',
            'subHeader' => 'cadastre as viagens dos eventos.',
            'createButtonLabel' => 'Adicionar Viagem',
            'createActionEventName' => 'events.trips.trip-create',
            'searchVisible' => false
        ];
    }


    #[On('events.trips.trip-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::events.trips.trip-form :eventId="$this->eventId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Motorista</flux:table.column>
            <flux:table.column sortable>Lotacão</flux:table.column>
            <flux:table.column sortable>Origem</flux:table.column>
            <flux:table.column sortable>Partida</flux:table.column>
            <flux:table.column sortable>Destino</flux:table.column>
            <flux:table.column sortable>Chegada</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $trip)
            <flux:table.row :key="$trip->id">
                <flux:table.cell>{{ $trip->id }}</flux:table.cell>
                <flux:table.cell>{{ $trip->event_driver->name }}</flux:table.cell>
                <flux:table.cell>{{ $trip->capacity() }}</flux:table.cell>
                <flux:table.cell>{{ $trip->from }}</flux:table.cell>
                <flux:table.cell>{{ App\Utils\DateUtil::formatDateTimeToBr($trip->start_date) }}</flux:table.cell>
                <flux:table.cell>{{ $trip->to }}</flux:table.cell>
                <flux:table.cell>{{ App\Utils\DateUtil::formatDateTimeToBr($trip->end_date) }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button href="{{ $eventId }}/trip/{{ $trip->id }}" icon="document-text" style="cursor: pointer;" wire:navigate
                            size="sm" />
                        <flux:button wire:click="$dispatch('events.trips.trip-edit', { id: {{ $trip->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $trip->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$trip->descriptor()}}', callbackDeleteEvent: 'events.trips.trip-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>