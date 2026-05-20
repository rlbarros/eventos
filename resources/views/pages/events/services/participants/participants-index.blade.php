<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Service\Partipants\WithEventServiceParticipantsProperties;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventServiceParticipantsProperties;

    public Collection $participants;

    public function indexArray(): array
    {
        return [
            'header' => 'Requisitantes do Serviço',
            'subHeader' => 'cadastre os requisitantes dos serviços.',
            'createButtonLabel' => 'Adicionar Requisitante',
            'createActionEventName' => 'events.services.participants.participant-create'
        ];
    }


    #[On('events.services.participants.participant-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::events.services.participants.participant-form :eventId="$this->eventId" :serviceId="$this->serviceId" />
    <livewire:pages::events.services.participants.payments.payments-index :eventId="$this->eventId" :serviceId="$this->serviceId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Participante</flux:table.column>
            <flux:table.column sortable>Valor Pago</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->participants as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->person->descriptor() }}</flux:table.cell>
                <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($participant->amount, true) }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button wire:click="$dispatch('events.services.participants.payments-view', { personId: {{ $participant->person_id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>