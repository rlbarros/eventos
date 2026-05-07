<?php

namespace App\Livewire;

use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public bool $readonly = false;
    public bool $touched = false;
    public string $label = '';
    public string $searchTerm = '';
    public string $lastSearchTerm = '';

    #[Modelable]
    public object $form;

    public Collection $persons;

    public function invalid()
    {
        return $this->touched = true && empty($this->persons);
    }

    public function hasTwoPersonsOrMore()
    {
        return $this->persons->count() >= 2;
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
            $this->lastSearchTerm = '';
            $this->persons = new Collection();
            $this->form->person_id = 0;
            return;
        }

        $this->touched = true;
        if ($this->persons->isNotEmpty() && $this->persons->hasSole('name', '=', $searchTerm)) {
            $this->form->person_id = $this->persons->where('name', '=', $searchTerm)->first()->id;
            $this->dispatchSelections();
            return;
        }

        $searchTerm = trim(strtolower($searchTerm));

        $isLastSearchTermPrefix = str_starts_with($searchTerm, $this->lastSearchTerm);
        $isPersonsHasAtLeastTwoItems = $this->persons->count() >= 2;
        $isLastSearchTermLowerThanCurrent = strlen($this->lastSearchTerm) < strlen($searchTerm);

        try {
            if ($this->touched && $isPersonsHasAtLeastTwoItems && $isLastSearchTermPrefix && $isLastSearchTermLowerThanCurrent) {
                $this->persons = $this->persons->filter(function ($person) use ($searchTerm) {
                    return str_starts_with(strtolower($person->name), $searchTerm);
                });
            } else {
                $this->dispatch('log-event', ['obj' => $searchTerm, 'level' => 'info']);
                $this->persons = Person::orderBy('name')
                    ->whereRaw('LOWER(name) LIKE ?', ["{$searchTerm}%"])
                    ->get();
                $this->dispatch('log-event', ['obj' => $this->persons, 'level' => 'info']);
            }
            $this->lastSearchTerm = $searchTerm;

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
    public function handleStateeventSiteExternalySelected(int $personId)
    {
        $this->form->person_id = $personId;
    }


    public function dispatchSelections()
    {
        $this->dispatch('person-selected', personId: $this->form->person_id);
    }
}

?>

<flux:field class="w-full">
    <flux:label>{{ ucfirst(str_replace('_', ' ', $label)) }}</flux:label>


    <flux:input wire:model.live="searchTerm" wire:keydown.debounce.300ms="loadPersons" :invalid="$this->invalid()" :disabled="$readonly" list="persons-list" autocomplete="off" />


    <datalist id="persons-list" class="w-full" style="max-height: 200px; overflow-y: auto;" wire:model.live="form.person_id" wire:change="dispatchSelections">
        @if($this->hasTwoPersonsOrMore())
        @foreach ($persons as $person)
        <option id="person-{{$person->id}}">{{$person->descriptor()}}</option>;
        @endforeach
        @endif
    </datalist>

</flux:field>