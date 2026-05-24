<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Participant\Service\WithEventParticipantServiceConsumptionProperties;
use Livewire\Attributes\On;

new class extends GenericIndexComponent
{
    use WithEventParticipantServiceConsumptionProperties;


    public function indexArray(): array
    {
        return [
            'header' => 'Participantes',
            'subHeader' => '',
            'createButtonLabel' => 'imprimir serviços',
            'createActionEventName' => 'print-services',
        ];
    }

    #[On('print-services')]
    public function handlePrintServices()
    {
        $url = route('exports.services', ['eventId' => $this->eventId, 'format' => 'print']);
        $this->js("window.open('{$url}', '_blank')");
    }
};

?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Serviço</flux:table.column>
            <flux:table.column sortable>Valor</flux:table.column>
            <flux:table.column sortable>Requisitante</flux:table.column>
            <flux:table.column sortable>Data de Pagamento</flux:table.column>
            <flux:table.column sortable>Valor Pago</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->index() as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->event_service->name }}</flux:table.cell>
                <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($participant->event_service->fee, true) }}</flux:table.cell>
                <flux:table.cell>{{ $participant->person->descriptor() }}</flux:table.cell>
                <flux:table.cell>{{ \App\Utils\DateUtil::formatDateToBr($participant->payment_date) }}</flux:table.cell>
                <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($participant->amount, true) }}</flux:table.cell>
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