<?php

use App\Models\EventSite;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;


new #[Title('Locais de Evento')] class extends Component
{
    use WithPagination;



    #[Computed]
    public function index()
    {
        return EventSite::latest()->paginate(10);
    }

    #[On('forms.event-sites.event-site-delete-confirmed')]
    public function handleEventSiteDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<x-pages::forms.layout>
    <div class="w-full mx-auto space-y-4">
        <div class="flex items-start max-md:flex-col">
            <div class="flex-1">
                <flux:heading sixe="xl" level="1">{{ __('Locais de Evento') }}</flux:heading>
                <flux:subheading size="lg" class="mb-4">{{ __('cadastre as chácaras, estâncias ou quaisquer outros
                    locais de recepção onde ocorrem os eventos da IEA.') }}</flux:subheading>
            </div>

            <flux:button variant="primary" wire:click="$dispatch('forms.event-sites.event-site-create')">
                Criar Local de Evento
            </flux:button>

            <livewire:pages::forms.event-sites.event-site-form />
        </div>
    </div>
    <flux:separator variant="subtle" />
    <div class="overflow-x-auto">
        <flux:table :paginate="$this->index()" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column sortable sorted direction="desc">#</flux:table.column>
                <flux:table.column sortable>Nome</flux:table.column>
                <flux:table.column sortable>Telefone</flux:table.column>
                <flux:table.column sortable>Endereço</flux:table.column>
                <flux:table.column sortable>Cidade</flux:table.column>
                <flux:table.column sortable>Estado</flux:table.column>
                <flux:table.column sortable>Ações</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->index() as $eventSite)
                <flux:table.row :key="$eventSite->id">
                    <flux:table.cell>{{ $eventSite->id }}</flux:table.cell>
                    <flux:table.cell>{{ $eventSite->name }}</flux:table.cell>
                    <flux:table.cell>{{ $eventSite->phone }}</flux:table.cell>
                    <flux:table.cell>{{ $eventSite->address }}</flux:table.cell>
                    <flux:table.cell>{{ $eventSite->city->name }}</flux:table.cell>
                    <flux:table.cell>{{ $eventSite->state->name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-3">
                            <flux:button href="event-site-detail/{{ $eventSite->id }}" icon="document-text" style="cursor: pointer;" wire:navigate
                                size="sm" />
                            <flux:button wire:click="$dispatch('forms.event-sites.event-site-view', { id: {{ $eventSite->id }} })" icon="document-magnifying-glass" style="cursor: pointer;"
                                size="sm" />
                            <flux:button wire:click="$dispatch('forms.event-sites.event-site-edit', { id: {{ $eventSite->id }} })" icon="pencil-square" style="cursor: pointer;"
                                size="sm" />
                            <flux:button variant="danger" icon="trash" size="sm"
                                wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $eventSite->id }}, modelName: '{{EventSite::modelName()}}', descriptor: '{{$eventSite->descriptor()}}', callbackDeleteEvent: 'forms.event-sites.event-site-delete-confirmed' })" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    <livewire:dialogs::delete-confirmation />
</x-pages::forms.layout>