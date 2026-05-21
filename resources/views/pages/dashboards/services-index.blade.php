<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Participant\Service\WithEventParticipantServiceConsumptionProperties;


new class extends GenericIndexComponent
{
    use WithEventParticipantServiceConsumptionProperties;


    public function indexArray(): array
    {
        return [
            'header' => 'Participantes',
            'subHeader' => '',
            'createButtonLabel' => '',
            'createActionEventName' => '',
            'createButtonVisible' => false
        ];
    }
}; ?>


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
            @foreach ($this->index() as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>{{ $participant->event_service->name }}</flux:table.cell>
                <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($participant->event_service->fee, true) }}</flux:table.cell>
                <flux:table.cell>{{ $participant->person->descriptor() }}</flux:table.cell>
                <flux:table.cell>{{ \App\Utils\DateUtil::formatDateToBr($participant->payment_date) }}</flux:table.cell>
                <flux:table.cell>{{ \App\Utils\CurrencyUtil::formatCurrencyToBr($participant->amount, true) }}</flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>