<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Participant\WithEventParticipantAllocatedProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventParticipantAllocatedProperties;


    public function indexArray(): array
    {
        return [
            'header' => 'Participantes',
            'subHeader' => '',
            'createButtonLabel' => 'Adicionar Participante',
            'createActionEventName' => 'events.participants.participant-create',
            'createButtonVisible' => false
        ];
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Participante</flux:table.column>
            <flux:table.column sortable>Tipo de Quarto</flux:table.column>
            <flux:table.column sortable>Quarto</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->descriptor() }}</flux:table.cell>
                <flux:table.cell>{{ $participant->event_site_room_type->name }}</flux:table.cell>
                <flux:table.cell>{{ $participant->event_site_room->name }}</flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>