<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Event\WithEventProperties;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

new #[Title('Eventos')] class extends GenericIndexComponent {

    use WithEventProperties;

    public function indexArray(): array
    {
        return [
            'header' => 'Eventos',
            'subHeader' => 'Gerencie os eventos cadastrados',
            'createButtonLabel' => 'Criar Evento',
            'createActionEventName' => 'events.event-create',
        ];
    }



    #[On('events.event-delete-confirmed')]
    public function handleEventDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
};
?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:pages::events.event-form />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Nome</flux:table.column>
            <flux:table.column sortable>Data de Início</flux:table.column>
            <flux:table.column sortable>Data de Fim</flux:table.column>
            <flux:table.column sortable>Igreja</flux:table.column>
            <flux:table.column sortable>Local de Evento</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $event)
            <flux:table.row :key="$event->id">
                <flux:table.cell>{{ $event->id }}</flux:table.cell>
                <flux:table.cell>{{ $event->name }}</flux:table.cell>
                <flux:table.cell>{{ App\Utils\DateUtil::formatDateToBr($event->start_date) }}</flux:table.cell>
                <flux:table.cell>{{ App\Utils\DateUtil::formatDateToBr($event->end_date) }}</flux:table.cell>
                <flux:table.cell>{{ $event->church->name }}</flux:table.cell>
                <flux:table.cell>{{ $event->event_site->name }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button href="events/event-detail/{{ $event->id }}?selectedTab=participants-tab" icon="document-text" style="cursor: pointer;" wire:navigate
                            size="sm" />
                        <flux:button wire:click="$dispatch('events.event-view', { id: {{ $event->id }} })" icon="document-magnifying-glass" style="cursor: pointer;"
                            size="sm" />
                        <flux:button wire:click="$dispatch('events.event-edit', { id: {{ $event->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $event->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$event->descriptor()}}', callbackDeleteEvent: 'events.event-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>