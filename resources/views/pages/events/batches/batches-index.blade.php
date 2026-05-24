<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\Batch\WithEventBatchProperties;
use Livewire\Attributes\On;


new class extends GenericIndexComponent
{
    use WithEventBatchProperties;


    public function indexArray(): array
    {
        return [
            'header' => 'Lotes',
            'subHeader' => 'cadastre os lotes dos eventos.',
            'createButtonLabel' => 'Adicionar Lote',
            'createActionEventName' => 'events.batches.batch-create',
            'searchVisible' => false
        ];
    }


    #[On('events.batches.batch-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::events.batches.batch-form :eventId="$this->eventId" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Lote</flux:table.column>
            <flux:table.column sortable>Data de Início</flux:table.column>
            <flux:table.column sortable>Data de Fim</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->index() as $batch)
            <flux:table.row :key="$batch->id">
                <flux:table.cell>{{ $batch->id }}</flux:table.cell>
                <flux:table.cell>{{ $batch->batch }}</flux:table.cell>
                <flux:table.cell>{{ App\Utils\DateUtil::formatDateToBr($batch->start_date) }}</flux:table.cell>
                <flux:table.cell>{{ App\Utils\DateUtil::formatDateToBr($batch->end_date) }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button wire:click="$dispatch('events.batches.batch-edit', { id: {{ $batch->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $batch->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$batch->descriptor()}}', callbackDeleteEvent: 'events.batches.batch-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="2" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                    Sem lotes programados
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>