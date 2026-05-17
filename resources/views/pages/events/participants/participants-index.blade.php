<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Participant\WithEventParticipantProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventParticipantProperties;


    public function indexArray(): array
    {
        return [
            'header' => 'Participantes',
            'subHeader' => 'cadastre os participantes dos eventos.',
            'createButtonLabel' => 'Adicionar Participante',
            'createActionEventName' => 'events.participants.participant-create'
        ];
    }


    #[On('events.participants.participant-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::events.participants.participant-form :eventId="$this->eventId" :eventSiteId="$this->eventSiteId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Participante</flux:table.column>
            <flux:table.column sortable>Tipo de Quarto</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->descriptor() }}</flux:table.cell>
                <flux:table.cell>{{ $participant->event_site_room_type->name }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button href="{{ $eventId }}/participant/{{ $participant->id }}?selectedTab=payments-tab" icon="document-text" style="cursor: pointer;" wire:navigate
                            size="sm" />
                        <flux:button wire:click="$dispatch('events.participants.participant-edit', { id: {{ $participant->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $participant->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$participant->descriptor()}}', callbackDeleteEvent: 'events.participants.participant-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>