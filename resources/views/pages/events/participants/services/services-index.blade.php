<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Models\EventServiceParticipantConsumption;
use App\Models\EventServiceParticipantPayment;
use App\Traits\Forms\Event\Participant\Service\WithEventParticipantServiceProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventParticipantServiceProperties;

    public string $totalRequested;
    public string $totalPayed;
    public string $balance;

    public function mount()
    {
        $consumptions = EventServiceParticipantConsumption::where('event_id', $this->eventId)
            ->where('person_id', $this->personId)
            ->get();

        $cunsumptionsIds = $consumptions->map(function ($item) {
            return $item["id"];
        });

        $this->totalRequested = 0;
        foreach ($consumptions as $consumption) {
            $this->totalRequested += $consumption->totalFee();
        }

        $payments = EventServiceParticipantPayment::whereIn('consumption_id', $cunsumptionsIds);

        $this->totalPayed = $payments->sum('amount');
        $this->balance = max(0, $this->totalRequested - $this->totalPayed);
    }

    public function indexArray(): array
    {
        return [
            'header' => 'Serviços',
            'subHeader' => 'cadastre os serviços consumidos pelo participante.',
            'createButtonLabel' => 'Adicionar Serviço',
            'createActionEventName' => 'events.participants.services.service-create',
            'searchVisible' => false
        ];
    }


    #[On('events.participants.services.service-delete-confirmed')]
    public function handleParticipantServiceDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>

<div class="w-full mx-auto space-y-4">
    <flux:callout inline>
        <flux:callout.heading>
            <flux:heading size="sm">Total Requisitado: {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalRequested, true) }}</flux:heading>
            <flux:heading size="sm">Total Pago: {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalPayed, true) }}</flux:heading>
            <flux:heading size="sm">Saldo Devedor: {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($this->balance, true) }}</flux:heading>
        </flux:callout.heading>
    </flux:callout>

    <livewire:pages::forms.generic-list :indexArray="$this->indexArray()">

        <livewire:pages::events.participants.services.payments.payments-index :eventId="$this->eventId" :allocationId="$this->allocationId" :personId="$this->personId" />
        <livewire:pages::events.participants.services.service-form :eventId="$this->eventId" :allocationId="$this->allocationId" :personId="$this->personId" />

        <flux:table :paginate="$this->index()" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column sortable sorted direction="desc">#</flux:table.column>
                <flux:table.column sortable>Serviço</flux:table.column>
                <flux:table.column sortable>quantidade</flux:table.column>
                <flux:table.column sortable>Total Requisitado</flux:table.column>
                <flux:table.column sortable>Total Pago</flux:table.column>
                <flux:table.column sortable>Ações</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->index() as $consumption)
                <flux:table.row :key="$consumption->id">
                    <flux:table.cell>{{ $consumption->id }}</flux:table.cell>
                    <flux:table.cell>{{ $consumption->event_service->descriptor() }}</flux:table.cell>
                    <flux:table.cell>{{ intval($consumption->quantity) }}</flux:table.cell>
                    <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($consumption->totalFee(), true) }}</flux:table.cell>
                    <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($consumption->totalPaid(), true) }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-3">
                            <flux:button wire:click="$dispatch('events.participants.services.service-edit', { id: {{ $consumption->id }} })" icon="pencil-square" style="cursor: pointer;"
                                size="sm" />
                            <flux:button wire:click="$dispatch('events.services.participants.payments-view', { consumptionId: {{ $consumption->id }} })" icon="document-text" style="cursor: pointer;"
                                size="sm" />
                            @if(!$consumption->hasPayments())
                            <flux:button variant="danger" icon="trash" size="sm"
                                wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $consumption->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$consumption->descriptor()}}', callbackDeleteEvent: 'events.participants.services.service-delete-confirmed' })" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="2" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                        Sem serviços requisitados
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </livewire:pages::forms.generic-list>
</div>