<?php

use App\Models\Church;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

new #[Title('Igrejas')] class extends Component {

    use WithPagination;

    public function indexArray(): array
    {
        return [
            'header' => 'Igrejas',
            'subHeader' => 'Gerencie as igrejas cadastradas',
            'createButtonLabel' => 'Criar Igreja',
            'createActionEventName' => 'forms.churchs.church-create',
        ];
    }

    #[Computed]
    public function index()
    {
        return Church::latest()->paginate(10);
    }

    #[On('forms.church-delete-confirmed')]
    public function handleChurchDeleteConfirmed(int $id)
    {
        try {
            $Church = Church::findOrFail($id);
            $Church->delete();

            Toaster::success(Church::modelName() . $this->Church->descriptor() . ' excluída com sucesso');
            Flux::modal('dialogs.delete-confirmation')->close();
            $this->redirectRoute('churches');
        } catch (\Exception $e) {
            Toaster::warning('erro ' . $e->getMessage() . 'ao  apagar igreja ' . $Church->descriptor());
        }
    }
};
?>


<livewire:pages::forms.generic-index :indexArray="$this->indexArray()">





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
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $church->id }}, modelName: '{{church::modelName()}}', descriptor: '{{$church->descriptor()}}', callbackEvent: 'forms.church-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-index>