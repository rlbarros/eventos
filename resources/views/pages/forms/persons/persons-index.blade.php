<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Traits\Forms\Person\WithPersonProperties;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

new #[Title('Pessoas')] class extends GenericIndexComponent {

    use WithPersonProperties;

    public function indexArray(): array
    {
        return [
            'header' => 'Pessoas',
            'subHeader' => 'Gerencie as pessoas cadastradas',
            'createButtonLabel' => 'Criar Pessoa',
            'createActionEventName' => 'forms.persons.person-create'
        ];
    }



    #[On('forms.person-delete-confirmed')]
    public function handlePersonDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
};
?>


<livewire:pages::forms.generic-index :indexArray="$this->indexArray()">
    <livewire:pages::forms.persons.person-form />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Igreja</flux:table.column>
            <flux:table.column sortable>Função</flux:table.column>
            <flux:table.column sortable>Nome</flux:table.column>
            <flux:table.column sortable>Data de Nascimento</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->index() as $person)
            <flux:table.row :key="$person->id">
                <flux:table.cell>{{ $person->id }}</flux:table.cell>
                <flux:table.cell>{{ $person->church->name }}</flux:table.cell>
                <flux:table.cell>{{ $person->function }}</flux:table.cell>
                <flux:table.cell>{{ $person->name }}</flux:table.cell>
                <flux:table.cell>{{ App\Utils\DateUtil::formatDateToBr($person->birth_date) }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        <flux:button wire:click="$dispatch('forms.persons.person-view', { id: {{ $person->id }} })" icon="document-magnifying-glass" style="cursor: pointer;"
                            size="sm" />
                        <flux:button wire:click="$dispatch('forms.persons.person-edit', { id: {{ $person->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $person->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$person->descriptor()}}', callbackDeleteEvent: 'forms.person-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-index>