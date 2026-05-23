<?php

use App\Models\Person;
use Livewire\Attributes\Json;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {

    #[Reactive]
    public bool $readonly;

    #[Reactive]
    public int $personId;

    #[Reactive]
    public array $nonList = [];

    public string $query;

    #[On('person-injected')]
    public function handlePersonInjected()
    {
        if (empty($this->personId)) {
            $this->query = '';
        } else {
            $this->query = Person::find($this->personId)->name;
        }
    }

    #[Json]
    public function search(string $query)
    {
        if (str_contains($query, '|')) {
            $query = trim(explode('|', $query)[1]);
        }
        $persons = Person::where('name', 'like', "%{$query}%")
            ->whereNotIn('id', $this->nonList)
            ->limit(10)
            ->get();

        if ($persons->count() === 1) {
            $person = $persons->first();
            $this->dispatch('person-selected', $person->id);
        }


        $formattedPersons = $persons->map(function ($person) {
            return [
                'id' => $person->id,
                'name' => $person->name,
                'church' => $person->church->name
            ];
        });

        return $formattedPersons;
    }
};

?>

<div x-data="{ query: @entangle('query'), datalistVisible: false, persons: [] }">

    <flux:field class="w-full">
        <flux:label>Pessoa</flux:label>

        <flux:input x-model.debounce.300ms="query" wire:model="query" x-on:input.debounce.300ms="$wire.search(query).then(data => {persons = data; datalistVisible = data.length > 1})"
            list="persons-list" autocomplete="off" :readonly="$readonly" />

        <datalist id="persons-list" class="w-full hide-only-child" style="max-height: 200px; overflow-y: auto;" x-show="datalistVisible" x-cloak>
            <template x-for="person in persons">
                <option x-value="person.id" x-text="person.church + ' | ' + person.name"></option>
            </template>
        </datalist>

    </flux:field>
</div>