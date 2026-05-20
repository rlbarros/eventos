<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Models\EventServiceParticipantConsumption;
use App\Traits\Forms\Event\Participant\Service\WithEventParticipantServiceProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventParticipantServiceProperties;


    public string $totalPayed;
    public string $balance;

    public function mount()
    {
        $payments = EventServiceParticipantConsumption::where('event_id', $this->eventId)
            ->where('person_id', $this->personId)
            ->get();


        $this->totalPayed = $payments->sum('amount');
        $this->balance = 0;

        $calculatedServices = [];
        foreach ($payments as $payment) {
            $eventService = $payment->event_service;
            if (!in_array($eventService->id, $calculatedServices)) {
                $this->balance += $eventService->fee;
                array_push($calculatedServices, $eventService->id);
            }
        }

        $this->balance -= $this->totalPayed;
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
            <flux:heading size="sm">Total Pago: {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalPayed, true) }}</flux:heading>
            <flux:heading size="sm">Saldo Devedor: {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($this->balance, true) }}</flux:heading>
        </flux:callout.heading>
    </flux:callout>

    <livewire:pages::forms.generic-list :indexArray="$this->indexArray()">

        <livewire:pages::events.participants.services.service-form
            :eventId="$this->eventId"
            :personId="$this->personId"
            :allocationId="$this->allocationId" />

        <flux:table :paginate="$this->index()" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column sortable sorted direction="desc">#</flux:table.column>
                <flux:table.column sortable>Serviço</flux:table.column>
                <flux:table.column sortable>Data de Pagamento</flux:table.column>
                <flux:table.column sortable>Valor</flux:table.column>
                <flux:table.column sortable>Ações</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->index() as $payment)
                <flux:table.row :key="$payment->id">
                    <flux:table.cell>{{ $payment->id }}</flux:table.cell>
                    <flux:table.cell>{{ $payment->event_service->descriptor() }}</flux:table.cell>
                    <flux:table.cell>{{ App\Utils\DateUtil::formatDateToBr($payment->payment_date) }}</flux:table.cell>
                    <flux:table.cell>{{ $payment->amount }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-3">
                            <flux:button wire:click="$dispatch('events.participants.services.service-edit', { id: {{ $payment->id }} })" icon="pencil-square" style="cursor: pointer;"
                                size="sm" />
                            <flux:button variant="danger" icon="trash" size="sm"
                                wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $payment->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$payment->descriptor()}}', callbackDeleteEvent: 'events.participants.services.service-delete-confirmed' })" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </livewire:pages::forms.generic-list>
</div>