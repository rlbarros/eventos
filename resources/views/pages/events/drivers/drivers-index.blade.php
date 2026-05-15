<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Driver\WithEventDriverProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventDriverProperties;


    public function indexArray(): array
    {
        return [
            'header' => 'Lotes',
            'subHeader' => 'cadastre os motoristas de tranporte do evento.',
            'createButtonLabel' => 'Adicionar Motorista',
            'createActionEventName' => 'events.drivers.driver-create'
        ];
    }


    #[On('events.drivers.driver-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::events.drivers.driver-form :eventId="$this->eventId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Nome</flux:table.column>
            <flux:table.column sortable>Telefone</flux:table.column>
            <flux:table.column sortable>Veículo</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $driver)
            <flux:table.row :key="$driver->id">
                <flux:table.cell>{{ $driver->id }}</flux:table.cell>
                <flux:table.cell>{{ $driver->name }}</flux:table.cell>
                <flux:table.cell>{{ $driver->phone }}</flux:table.cell>
                <flux:table.cell>{{ $driver->vehicle }} ( {{ $driver->capacidade }} Lugares)</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button wire:click="$dispatch('events.drivers.driver-edit', { id: {{ $driver->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $driver->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$driver->descriptor()}}', callbackDeleteEvent: 'events.drivers.driver-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>