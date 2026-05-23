<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Service\Partipants\WithEventServiceParticipantsProperties;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

new class extends GenericIndexComponent
{
    use WithEventServiceParticipantsProperties;

    public Collection $participants;

    public array $nonList;

    public function indexArray(): array
    {
        return [
            'header' => 'Requisitantes do Serviço',
            'subHeader' => 'cadastre os requisitantes dos serviços.',
            'createButtonLabel' => 'Adicionar Requisitante',
            'createActionEventName' => 'events.services.participants.participant-create'
        ];
    }

    #[Url(history: true)]
    public string $search = '';
    protected $listeners = ['search-updated' => '$refresh'];

    public function updatedSearch()
    {
        $this->resetPage();

        $js = "const url = new URL(window.location);
            url.searchParams.set('search', '" . $this->search . "');
            url.searchParams.set('page', 1);
            window.history.replaceState({}, '', url);
            window.location.reload();";
        $this->js($js);
    }


    #[On('events.services.participants.participant-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::events.services.participants.participant-form :eventId="$this->eventId" :serviceId="$this->serviceId" :nonList="$this->nonList" />
    <livewire:pages::events.services.participants.payments.payments-index :eventId="$this->eventId" :serviceId="$this->serviceId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Participante</flux:table.column>
            <flux:table.column sortable>Quantidade</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->participants as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->person->descriptor() }}</flux:table.cell>
                <flux:table.cell>{{ intval($participant->quantity) }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button wire:click="$dispatch('events.services.participants.payments-view', { consumptionId: {{ $participant->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        @if (!$participant->hasPayments())
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $participant->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$participant->descriptor()}}', callbackDeleteEvent: 'events.services.participants.participant-delete-confirmed' })" />
                        @endif
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>