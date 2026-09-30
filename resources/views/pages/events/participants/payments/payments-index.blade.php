<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Models\Event;
use App\Models\EventFee;
use App\Models\EventParticipantAllocation;
use App\Models\EventParticipantPayment;
use App\Services\Pricing\OccupancyPricing;
use App\Traits\Forms\Event\Participant\Payment\WithEventParticipantPaymentProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventParticipantPaymentProperties;

    public string $totalPayed;
    public string $balance;
    public int $occupancy = 1;
    public float $reservationTotal = 0;
    public ?string $payerName = null;

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

        $pricing = new OccupancyPricing();
        $allocation = EventParticipantAllocation::findOrFail($this->allocationId);
        $payer = $pricing->payerAllocation($allocation);

        // quem não é o pagador da reserva não deve nada: o total fica com o pagador
        if (!$payer->is($allocation)) {
            $this->payerName = $payer->person->name;
            return;
        }

        $lastBatchoffPayments = 0;
        foreach ($payments as $payment) {
            $eventFee = $eventFees->where('id', $payment->event_fee_id)->first();
            if ($eventFee->event_batch->batch > $lastBatchoffPayments) {
                $lastBatchoffPayments = $eventFee->event_batch->batch;
            }
        }

        if (!empty($lastBatchoffPayments)) {
            $batchFees = $eventFees->filter(fn ($item) => $item->event_batch->batch === $lastBatchoffPayments)->values();
        } else {
            $currentDate = now()->toDateString();
            $batchFees = $eventFees->filter(function ($item) use ($currentDate) {
                $eventBatch = $item->event_batch;
                return $eventBatch->start_date <= $currentDate &&  $currentDate <= $eventBatch->end_date;
            })->values();

            if ($batchFees->isEmpty()) {
                $lastBatch = $eventFees->max(fn ($item) => $item->event_batch->batch);
                $batchFees = $eventFees->filter(fn ($item) => $item->event_batch->batch === $lastBatch)->values();
            }
        }

        $event = Event::find($this->eventId);
        $this->occupancy = $pricing->reservation($payer)->count();
        $this->reservationTotal = $pricing->reservationTotal($payer, $batchFees, $event);

        // pagamentos de quem divide o quarto e paga pelo pagador entram no saldo da reserva
        $reservationPersonIds = $pricing->reservation($payer)->pluck('person_id');
        $this->totalPayed = EventParticipantPayment::where('event_id', $this->eventId)
            ->whereIn('person_id', $reservationPersonIds)
            ->sum('amount');

        $this->balance += max(0, $this->reservationTotal - $this->totalPayed);
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
            @if ($this->payerName)
            <flux:text size="sm">A reserva é paga por {{ $this->payerName }}, que responde pelo total.</flux:text>
            @elseif ($this->occupancy > 1)
            <flux:text size="sm">Reserva com {{ $this->occupancy }} pessoas: total {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($this->reservationTotal, true) }}. O saldo considera os pagamentos de todos da reserva.</flux:text>
            @endif
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