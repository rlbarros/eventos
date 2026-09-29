<?php

use App\Models\Church;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {

    #[Reactive]
    public bool $readonly;

    public string $query = '';

    #[On('church-injected')]
    public function handleChurchInjected(int $churchId)
    {
        $this->query = empty($churchId) ? '' : (Church::find($churchId)?->name ?? '');
    }

    public function search(string $query): array
    {
        return Church::where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn ($church) => ['id' => $church->id, 'label' => $church->name])
            ->all();
    }

    public function select(int $id): void
    {
        $this->dispatch('church-selected', $id);
    }
};

?>

<x-autocomplete label="Igreja" :readonly="$readonly" />
