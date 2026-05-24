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
            'createButtonLabel' => 'imprimir alocações',
            'createActionEventName' => 'print-allocations',
        ];
    }

    #[On('print-allocations')]
    public function handlePrintAllocations()
    {
        $url = route('exports.allocations', ['eventId' => $this->eventId, 'format' => 'print']);
        $this->js("window.open('{$url}', '_blank')");
    }
};

?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Participante</flux:table.column>
            <flux:table.column sortable>Tipo de Quarto</flux:table.column>
            <flux:table.column sortable>Quarto</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->index() as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->descriptor() }}</flux:table.cell>
                <flux:table.cell>{{ $participant->event_site_room_type->name }}</flux:table.cell>
                <flux:table.cell>{{ $participant->event_site_room->name }}</flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="2" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                    Sem participantes alocados
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>