<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Church\WithChurchProperties;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

new #[Title('Igrejas')] class extends GenericIndexComponent {

    use WithChurchProperties;

    public function indexArray(): array
    {
        return [
            'header' => 'Igrejas',
            'subHeader' => 'Gerencie as igrejas cadastradas',
            'createButtonLabel' => 'Criar Igreja',
            'createActionEventName' => 'forms.churchs.church-create'
        ];
    }



    #[On('forms.church-delete-confirmed')]
    public function handleChurchDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
};
?>


<livewire:pages::forms.generic-index :indexArray="$this->indexArray()">
    <livewire:pages::forms.churches.church-form />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Nome</flux:table.column>
            <flux:table.column sortable>Cidade</flux:table.column>
            <flux:table.column sortable>Estado</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $church)
            <flux:table.row :key="$church->id">
                <flux:table.cell>{{ $church->id }}</flux:table.cell>
                <flux:table.cell>{{ $church->name }}</flux:table.cell>
                <flux:table.cell>{{ $church->city->name }}</flux:table.cell>
                <flux:table.cell>{{ $church->state->name }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button wire:click="$dispatch('forms.churchs.church-view', { id: {{ $church->id }} })" icon="document-magnifying-glass" style="cursor: pointer;"
                            size="sm" />
                        <flux:button wire:click="$dispatch('forms.churchs.church-edit', { id: {{ $church->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $church->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$church->descriptor()}}', callbackDeleteEvent: 'forms.church-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-index>