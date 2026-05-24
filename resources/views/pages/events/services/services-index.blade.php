<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Service\WithEventServiceProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventServiceProperties;


    public function indexArray(): array
    {
        return [
            'header' => 'Serviços',
            'subHeader' => 'cadastre os serviçoes dos eventos.',
            'createButtonLabel' => 'Adicionar Serviço',
            'createActionEventName' => 'events.services.service-create'
        ];
    }


    #[On('events.services.service-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::events.services.service-form :eventId="$this->eventId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Nome</flux:table.column>
            <flux:table.column sortable>Taxa</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->index() as $service)
            <flux:table.row :key="$service->id">
                <flux:table.cell>{{ $service->id }}</flux:table.cell>
                <flux:table.cell>{{ $service->name }}</flux:table.cell>
                <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($service->fee) }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button href="{{ $eventId }}/service/{{ $service->id }}" icon="document-text" style="cursor: pointer;" wire:navigate
                            size="sm" />
                        <flux:button wire:click="$dispatch('events.services.service-edit', { id: {{ $service->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        @if (!$service->hasRequesters())
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $service->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$service->descriptor()}}', callbackDeleteEvent: 'events.services.service-delete-confirmed' })" />
                        @endif
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="2" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                    Sem serviços cadastrados
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>