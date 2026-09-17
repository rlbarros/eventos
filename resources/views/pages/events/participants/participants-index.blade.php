<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Models\Church;
use App\Models\EventParticipantAllocation;
use App\Traits\Forms\Event\Participant\WithEventParticipantProperties;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;


new class extends GenericIndexComponent
{
    use WithEventParticipantProperties;

    public array $nonList;

    public \Illuminate\Support\Collection $churches;

    #[Url(history: true)]
    public int $churchId = 0;

    public function mount()
    {
        $this->nonList = EventParticipantAllocation::where('event_id', $this->eventId)
            ->pluck('person_id')
            ->values()
            ->toArray();

        $this->churches = Church::orderBy('name')->get(['id', 'name']);
    }

    public function customQueryScope($query)
    {
        if (!empty($this->churchId)) {
            $query->whereHas('person', function ($personQuery) {
                $personQuery->where('church_id', $this->churchId);
            });
        }
        return $query;
    }

    public function updatedChurchId()
    {
        $this->resetPage();
    }


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
    <x-slot:extraFilters>
        <div class="w-64">
            <flux:select wire:model.live="churchId" wire:island="list">
                <flux:select.option value="0">Todas as igrejas</flux:select.option>
                @foreach ($churches as $church)
                <flux:select.option value="{{ $church->id }}">{{ $church->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </x-slot:extraFilters>

    <livewire:pages::events.participants.participant-form :eventId="$this->eventId" :eventSiteId="$this->eventSiteId" :nonList="$this->nonList" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Participante</flux:table.column>
            <flux:table.column sortable>Tipo de Quarto</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->index() as $participant)
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
            @empty
            <flux:table.row>
                <flux:table.cell colspan="2" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                    Sem participantes no evento
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>