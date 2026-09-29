<?php

use App\Models\Person;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {

    #[Reactive]
    public bool $readonly;

    #[Reactive]
    public array $nonList = [];

    public string $query = '';

    #[On('person-injected')]
    public function handlePersonInjected(int $personId)
    {
        $this->query = empty($personId) ? '' : (Person::find($personId)?->name ?? '');
    }

    public function search(string $query): array
    {
        return Person::with('church')
            ->where('name', 'like', "%{$query}%")
            ->whereNotIn('id', $this->nonList)
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn ($person) => [
                'id' => $person->id,
                'label' => $person->name,
                'sublabel' => $person->church?->name,
            ])
            ->all();
    }

    public function select(int $id): void
    {
        $this->dispatch('person-selected', $id);
    }
};

?>

<x-autocomplete label="Pessoa" :readonly="$readonly" />
