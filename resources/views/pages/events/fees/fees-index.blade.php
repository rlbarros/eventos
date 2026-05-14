<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Fee\WithEventFeeProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventFeeProperties;

    public function indexArray(): array
    {
        return [
            'header' => 'Taxas',
            'subHeader' => 'cadastre os taxas de inscrições dos evento.',
            'createButtonLabel' => 'Adicionar Taxa',
            'createActionEventName' => 'events.fees.fee-create',
            'searchVisible' => false
        ];
    }


    #[On('events.fees.fee-delete-confirmed')]
    public function handleFeeDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::events.fees.fee-form :eventId="$this->eventId" :eventSiteId="$this->eventSiteId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Tipo de Quarto</flux:table.column>
            <flux:table.column sortable>Lote</flux:table.column>
            <flux:table.column sortable>Categoria</flux:table.column>
            <flux:table.column sortable>Taxa</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $fee)
            <flux:table.row :key="$fee->id">
                <flux:table.cell>{{ $fee->id }}</flux:table.cell>
                <flux:table.cell>{{ $fee->event_site_room_type->name }}</flux:table.cell>
                <flux:table.cell>{{ $fee->event_batch->descriptor() }}</flux:table.cell>
                <flux:table.cell>{{ $fee ->category}}</flux:table.cell>
                <flux:table.cell>{{ $fee ->fee}}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button wire:click="$dispatch('events.fees.fee-edit', { id: {{ $fee->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $fee->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$fee->descriptor()}}', callbackDeleteEvent: 'events.fees.fee-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>