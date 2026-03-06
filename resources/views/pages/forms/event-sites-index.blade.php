<?php

use App\Models\EventSite;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;


new #[Title('Locais de Evento')] class extends Component
{
    use WithPagination;

    #[Computed]
    public function index()
    {
        return EventSite::latest()->paginate(10);
    }

    public function showEditForm(int $id)
    {
        dd($id);
    }

    public function delete(int $id)
    {
        dd($id);
    }
}; ?>


<x-pages::forms.layout>
    <div class="w-full mx-auto space-y-4">
        <div class="flex items-start max-md:flex-col">
            <div class="flex-1">
                <flux:heading sixe="xl" level="1">{{ __('Locais de Evento') }}</flux:heading>
                <flux:subheading size="lg" class="mb-4">{{ __('cadastre as chácaras, estâncias ou quaisquer outros
                    locais de receplção onde ocorrem os eventos da IEA.') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="create-event-site">
                <flux:button variant="primary">
                    Criar Local de Evento
                </flux:button>
            </flux:modal.trigger>

            <livewire:pages::forms.event-sites-post />
        </div>
    </div>
    <flux:separator variant="subtle" />
    <div class="overflow-x-auto">
        <flux:table :paginate="$this->index()" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column sortable sorted direction="desc">#</flux:table.column>
                <flux:table.column sortable>Nome</flux:table.column>
                <flux:table.column sortable>Telfone</flux:table.column>
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
                        <flux:button wire:click="showEditForm({{ $eventSite['id'] }})" icon="pencil-square" size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm" wire:click="delete({{ $eventSite['id'] }})" />
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</x-pages::forms.layout>