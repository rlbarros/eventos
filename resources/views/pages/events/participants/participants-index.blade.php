<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Participant\WithEventParticipantProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventParticipantProperties;

    public $eventId;

    public function indexArray(): array
    {
        return [
            'header' => 'Participantes',
            'subHeader' => 'cadastre os participantes dos eventos.',
            'createButtonLabel' => 'Criar Participante',
            'createActionEventName' => 'events.participants.participant-create'
        ];
    }

    public function customOrderingColumn(): string
    {
        return 'id';
    }

    public function customWhereIndex(): array
    {
        return [
            ['event_id', '=', $this->eventId]
        ];
    }

    #[On('events.participants.participant-delete-confirmed')]
    public function handleEventSiteRoomTypeDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::
        events.participants.participant-form :eventId="$this->eventId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Participante</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $participant)
            <flux:table.row :key="$roomType->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->descriptor() }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button href="event-detail/{{ $event->id }}/participant/{{ $participant->id }}?selectedTab=participants-tab" icon="document-text" style="cursor: pointer;" wire:navigate
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