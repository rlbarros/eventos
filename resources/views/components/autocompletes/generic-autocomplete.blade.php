<?php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component {


    public string $label = '';
    public string $idField = '';
    public string $searchField = '';
    public string $searchEvent = '';
    public string $selectedEvent = '';
    public string $idDataListName;
    public array $autocompleteArray = [];
    public bool $readonly;
    public bool $containsFilter = true; //true %like% false like%

    #[Modelable]
    public string $searchTerm;
    #[Modelable]
    public array $records;


    public function mount()
    {
        $this->label = $this->autocompleteArray['label'] ?? '';
        $this->idDataListName = strtolower($this->label) . '-autocomplete-list';
        $this->idField = $this->autocompleteArray['idField'] ?? '';
        $this->searchField = $this->autocompleteArray['searchField'] ?? '';
        $this->searchEvent = $this->autocompleteArray['searchEvent'] ?? '';
        $this->selectedEvent = $this->autocompleteArray['selectedEvent'] ?? '';
    }
}

?>

<flux:field class="w-full">
    <flux:label>{{ ucfirst(str_replace('_', ' ', $label)) }}</flux:label>

    <flux:input wire:model.live="searchTerm" wire:keydown.debounce.3000ms="$dispatch('{{$this->searchEvent}}', { term: '{{$this->searchTerm}}'})"
        :disabled="$readonly" list="{{$idDataListName}}" autocomplete="off" />

    <datalist id="{{$idDataListName}}" class="w-full" style="max-height: 200px; overflow-y: auto;">
        @foreach ($this->records as $record)
        <option id="{{$idDataListName}}-record-{{$record->id}}">{{$record->descriptor()}}</option>
        @endforeach
    </datalist>

</flux:field>