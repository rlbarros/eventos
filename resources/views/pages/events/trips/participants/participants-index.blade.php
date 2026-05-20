<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Trip\Participants\WithEventTripParticipantsProperties;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;

new class extends GenericIndexComponent
{
    use WithEventTripParticipantsProperties;

    public Collection $participants;

    public function indexArray(): array
    {
        return [
            'header' => 'Participantes da viagem',
            'subHeader' => 'cadastre os passageiros das viagens.',
            'createButtonLabel' => 'Adicionar Passageiro',
            'createActionEventName' => 'events.trips.participants.participant-create'
        ];
    }



    #[On('events.trips.participants.participant-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:dialogs::delete-confirmation />
    <livewire:pages::events.trips.participants.participant-form :eventId="$this->eventId" :tripId="$this->tripId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Participante</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->person->descriptor() }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $participant->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$participant->descriptor()}}', callbackDeleteEvent: 'events.trips.participants.participant-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>