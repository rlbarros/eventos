<?php

use App\Models\Church;
use Livewire\Attributes\Json;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {

    #[Reactive]
    public bool $readonly;

    #[Reactive]
    public int $churchId;

    public string $query;

    #[On('church-injected')]
    public function handleChurchInjected()
    {
        if (empty($this->churchId)) {
            $this->query = '';
        } else {
            $this->query = Church::find($this->churchId)->name;
        }
    }

    #[Json]
    public function search(string $query)
    {
        $churches = Church::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        if ($churches->count() === 1) {
            $church = $churches->first();
            $this->dispatch('church-selected', $church->id);
        }

        return $churches;
    }
};

?>

<div x-data="{ query: @entangle('query'), churches: [] }">

    <flux:field class="w-full">
        <flux:label>Igreja</flux:label>

        <flux:input x-model.debounce.300ms="query" wire:model="query" x-on:input.debounce.300ms="$wire.search(query).then(data => churches = data)"
            list="churches-list" autocomplete="off" :readonly="$readonly" />

        <datalist id="churches-list" class="w-full" style="max-height: 200px; overflow-y: auto;">
            <template x-for="church in churches">
                <option x-value="church.id" x-text="church.name"></option>
            </template>
        </datalist>

    </flux:field>
</div>