<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Models\Event;
use App\Models\EventFee;
use App\Models\EventParticipantPayment;
use App\Models\Person;
use App\Traits\Forms\Event\Participant\Payment\WithEventParticipantPaymentProperties;
use App\Utils\AgeUtil;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventParticipantPaymentProperties;

    public string $totalPayed;
    public string $balance;

    public function mount()
    {
        $eventFees = EventFee::where('event_id', $this->eventId)
            ->where('event_site_room_type_id', $this->eventSiteRoomTypeId)
            ->with('event_batch')
            ->get();

        $payments = EventParticipantPayment::where('event_id', $this->eventId)
            ->where('person_id', $this->personId)
            ->get();


        $this->totalPayed = $payments->sum('amount');
        $this->balance = 0;

        $lastBatchoffPayments = 0;
        foreach ($payments as $payment) {

            $eventFee = $eventFees->where('id', $payment->event_fee_id)->first();
            if ($eventFee->event_batch->batch > $lastBatchoffPayments) {
                $lastBatchoffPayments = $eventFee->event_batch->batch;
            }
        }

        $lastFee = 0;
        if (!empty($lastBatchoffPayments)) {
            $eventFeeOfLastBatchOffPayments = $eventFees->where('event_batch.batch', $lastBatchoffPayments)->first();
            $lastFee = $eventFeeOfLastBatchOffPayments->fee;
        } else {
            $currentDate = now()->toDateString();
            $currentEventFees = $eventFees->filter(function ($item) use ($currentDate) {
                $eventBatch = $item->event_batch;
                return $eventBatch->start_date <= $currentDate &&  $currentDate <= $eventBatch->end_date;
            })->values();

            if (empty($currentEventFees) || $currentEventFees->count() === 0) {
                $lastBatch = $eventFees->max(function ($item) {
                    return $item->event_batch->batch;
                });

                $currentEventFees = $eventFees->filter(function ($item) use ($lastBatch) {
                    return $item->event_batch->batch === $lastBatch;
                })->values();
            }

            $event = Event::find($this->eventId);
            $person = Person::find($this->personId);
            $currentEventFees =  AgeUtil::filterEventFeesByAge($currentEventFees, $person, $event);
            $eventFee = $currentEventFees->first();

            $lastFee = $eventFee->fee;
        }

        $this->balance += max(0, $lastFee - $this->totalPayed);
    }

    public function indexArray(): array
    {
        return [
            'header' => 'Pagamentos',
            'subHeader' => 'cadastre os pagamentos dos participantes.',
            'createButtonLabel' => 'Adicionar Pagamento',
            'createActionEventName' => 'events.participants.payments.payment-create',
            'searchVisible' => false
        ];
    }


    #[On('events.participants.payments.payment-delete-confirmed')]
    public function handleParticipantPaymentDeleteConfirmed(int $id)
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
        <livewire:pages::events.participants.payments.payment-form
            :eventId="$this->eventId"
            :personId="$this->personId"
            :allocationId="$this->allocationId"
            :eventSiteRoomTypeId="$this->eventSiteRoomTypeId" />

        <flux:table :paginate="$this->index()" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column sortable sorted direction="desc">#</flux:table.column>
                <flux:table.column sortable>Data de Pagamento</flux:table.column>
                <flux:table.column sortable>Valor</flux:table.column>
                <flux:table.column sortable>Ações</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->index() as $payment)
                <flux:table.row :key="$payment->id">
                    <flux:table.cell>{{ $payment->id }}</flux:table.cell>
                    <flux:table.cell>{{ App\Utils\DateUtil::formatDateToBr($payment->payment_date) }}</flux:table.cell>
                    <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($payment->amount, true) }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-3">
                            <flux:button wire:click="$dispatch('events.participants.payments.payment-edit', { id: {{ $payment->id }}, personId: {{ $payment->person_id }} })" icon="pencil-square" style="cursor: pointer;"
                                size="sm" />
                            <flux:button variant="danger" icon="trash" size="sm"
                                wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $payment->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$payment->descriptor()}}', callbackDeleteEvent: 'events.payments.payment-delete-confirmed' })" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="2" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                        Sem pagamentos realizados
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </livewire:pages::forms.generic-list>
</div>