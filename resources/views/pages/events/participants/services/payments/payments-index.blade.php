<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Models\EventServiceParticipantPayment;
use App\Traits\Forms\Event\Participant\Service\WithEventParticipantServicePaymentProperties;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventParticipantServicePaymentProperties;

    public Collection $payments;

    public function mount()
    {
        $this->payments = new Collection();
    }

    public function modalName(): string
    {
        return 'events.services.participants.payments';
    }

    #[On('events.services.participants.payments-view')]
    public function handleEventSiteViewRequest(int $consumptionId)
    {
        $this->consumptionId = $consumptionId;
        $this->payments = EventServiceParticipantPayment
            ::where('consumption_id', $this->consumptionId)
            ->get();
        Flux::modal($this->modalName())->show();
    }

    public function indexArray(): array
    {
        return [
            'header' => 'Pagamentos do Requisitante',
            'subHeader' => 'cadastre os pagamentos dos requisitantes.',
            'createButtonLabel' => 'Adicionar Pagamento',
            'createActionEventName' => 'events.services.participants.payment-create',
            'searchVisible' => false
        ];
    }


    #[On('events.services.participants.payment-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
};
?>


<flux:modal :name="$this->modalName()" class="md:w-650">
    <livewire:dialogs::delete-confirmation />
    <livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
        <livewire:pages::events.participants.services.payments.payment-form :eventId="$this->eventId" :allocationId="$this->allocationId" :consumptionId="$this->consumptionId" />

        <flux:table :paginate="$this->index()" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column sortable sorted direction="desc">#</flux:table.column>
                <flux:table.column sortable>Data de Pagamento</flux:table.column>
                <flux:table.column sortable>Valor Pago</flux:table.column>
                <flux:table.column sortable>Ações</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->payments as $payment)
                <flux:table.row :key="$payment->id">
                    <flux:table.cell>{{ $payment->id }}</flux:table.cell>
                    <flux:table.cell>{{ \App\Utils\DateUtil::formatDateToBr($payment->payment_date) }}</flux:table.cell>
                    <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($payment->amount) }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-3">
                            <flux:button wire:click="$dispatch('events.services.participants.payment-edit', { id: {{ $payment->id }}, personId: {{ $payment->person_id }} })" icon="pencil-square" style="cursor: pointer;"
                                size="sm" />
                            <flux:button variant="danger" icon="trash" size="sm"
                                wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $payment->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$payment->descriptor()}}', callbackDeleteEvent: 'events.services.participants.payment-delete-confirmed' })" />
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
</flux:modal>