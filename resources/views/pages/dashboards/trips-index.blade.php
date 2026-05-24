<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Participant\Trip\WithEventTripParticipantsProperties;
use Livewire\Attributes\On;

new class extends GenericIndexComponent
{
    use WithEventTripParticipantsProperties;


    public function indexArray(): array
    {
        return [
            'header' => 'Viagens',
            'subHeader' => '',
            'createButtonLabel' => 'imprimir viagens',
            'createActionEventName' => 'print-trips',
        ];
    }

    #[On('print-trips')]
    public function handlePrintTrips()
    {
        $url = route('exports.trips', ['eventId' => $this->eventId, 'format' => 'print']);
        $this->js("window.open('{$url}', '_blank')");
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Viagem</flux:table.column>
            <flux:table.column sortable>Passageiro</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->index() as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->event_trip->descriptor() }}</flux:table.cell>
                <flux:table.cell>{{ $participant->person->descriptor() }}</flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="2" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                    Sem viagens agendadas
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>