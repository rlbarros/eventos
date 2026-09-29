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
        // só o formulário de evento usa este campo: anfitrião de igreja/superintendência vê
        // apenas as igrejas da própria jurisdição
        $allowed = auth()->user()?->hostJurisdiction()->manageableAdministrationChurchIds();

        return Church::where('name', 'like', "%{$query}%")
            ->when($allowed !== null, fn ($q) => $q->whereIn('administration_system_id', $allowed->all() ?: [0]))
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
