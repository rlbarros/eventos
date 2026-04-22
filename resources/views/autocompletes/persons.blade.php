<?php

namespace App\Livewire;

use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public bool $readonly = false;
    public bool $touched = false;
    public string $fieldName = '';
    public string $label = '';
    public string $searchTerm = '';

    #[Modelable]
    public $form;

    public Collection $persons;

    public function invalid()
    {
        return $this->touched = true && empty($this->persons);
    }


    public function mount()
    {
        $this->persons = new Collection();
    }

    public function loadPersons()
    {
        $searchTerm = $this->searchTerm;

        // Check if search term contains "|" and take first element if it does
        if (strpos($searchTerm, '|') !== false) {
            $searchTerm = explode('|', $searchTerm)[0];
        }

        if (strlen($searchTerm) < 3) {
            $this->persons = new Collection();
            $this->form->person_id = 0;
            return;
        }

        $this->touched = true;
        if ($this->persons->isNotEmpty() && $this->persons->hasSole('name', '=', $searchTerm)) {
            $this->form->person_id = $this->persons->where('name', '=', $searchTerm)->first()->id;
            dd($this->form->person_id, $this->persons);
            $this->dispatchSelections();
            return;
        }

        $searchTerm = trim(strtolower($searchTerm));

        try {
            $this->dispatch('log-event', ['obj' => $searchTerm, 'level' => 'info']);
            $this->persons = Person::orderBy('name')
                ->whereRaw('LOWER(name) LIKE ?', ["{$searchTerm}%"])
                ->get();
            $this->dispatch('log-event', ['obj' => $this->persons, 'level' => 'info']);

            if ($this->persons->hasSole()) {
                $pessoa = $this->persons->first();
                $this->form->person_id = $pessoa->id;
                $this->searchTerm = $pessoa->descriptor();
                $this->dispatchSelections();
            }
        } catch (\Exception $e) {
            Toaster::warning('não foi possível consultar informações de pessoas');
            Log::error('error consulting persons ' . $e->getMessage(), $e->getTrace());
        }
    }

    #[On('person-externaly-selected')]
    public function handleStateeventSiteExternalySelected($personId)
    {
        $this->form->person_id = $personId;
    }


    public function dispatchSelections()
    {
        $this->dispatch('person-internaly-selected', personId: $this->form->person_id);
    }
}

?>

<flux:field class="w-full">
    <flux:label>{{ ucfirst(str_replace('_', ' ', $label)) }}</flux:label>


    <flux:input wire:model.live="searchTerm" wire:keydown.debounce.300ms="loadPersons" :invalid="$this->invalid()" :disabled="$readonly" list="persons-list" />


    <datalist id="persons-list" class="w-full" style="max-height: 200px; overflow-y: auto;" wire:model.live="form.person_id" wire:change="dispatchSelections">
        @if($persons->isEmpty())
        <option id="non-found-person" value="Nenhuma pessoa encontrada"></option>
        @else
        @foreach ($persons as $person)
        <option id="person-{{$person->id}}" value="{{$person->descriptor()}}"></option>;
        @endforeach
        @endif
    </datalist>

</flux:field>