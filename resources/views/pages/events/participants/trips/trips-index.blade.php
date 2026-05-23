<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Models\EventTripParticipant;
use App\Traits\Forms\Event\Participant\Trip\WithEventParticipantTripProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventParticipantTripProperties;

    public array $nonList;

    public function mount()
    {
        $this->nonList = EventTripParticipant::where('event_id', $this->eventId)
            ->where('person_id', $this->personId)
            ->pluck('event_trip_id')
            ->values()
            ->toArray();
    }

    public function indexArray(): array
    {
        return [
            'header' => 'Viagens',
            'subHeader' => 'cadastre as viagens do participante.',
            'createButtonLabel' => 'Adicionar Viagem',
            'createActionEventName' => 'events.participants.trips.trip-create',
            'searchVisible' => false
        ];
    }


    #[On('events.participants.trips.trip-delete-confirmed')]
    public function handleParticipantTripDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>



<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">

    <livewire:pages::events.participants.trips.trip-form
        :eventId="$this->eventId"
        :personId="$this->personId"
        :allocationId="$this->allocationId"
        :nonList="$this->nonList" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Viagem</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $tripParticipant)
            <flux:table.row :key="$tripParticipant->id">
                <flux:table.cell>{{ $tripParticipant->id }}</flux:table.cell>
                <flux:table.cell>{{ $tripParticipant->event_trip->descriptor() }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $tripParticipant->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$tripParticipant->descriptor()}}', callbackDeleteEvent: 'events.participants.trips.trip-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>