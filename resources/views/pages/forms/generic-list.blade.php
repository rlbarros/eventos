<?php

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {

    use WithPagination;

    public array $indexArray;
    public string $header;
    public string $subHeader;
    public string $createButtonLabel;
    public string $createActionEventName;

    #[Url(history: true)]
    public string $search;


    public function updatedSearch()
    {
        $this->resetPage();

        $js = "const url = new URL(window.location);
            url.searchParams.set('search', '" . $this->search . "');
            url.searchParams.set('page', 1);
            window.history.replaceState({}, '', url);
            window.location.reload();";
        $this->js($js);
    }

    public function mount()
    {
        $this->header = $this->indexArray['header'] ?? null;
        $this->subHeader = $this->indexArray['subHeader'] ?? null;
        $this->createButtonLabel = $this->indexArray['createButtonLabel'] ?? null;
        $this->createActionEventName = $this->indexArray['createActionEventName'] ?? null;
    }
};

?>

<div class="w-full mx-auto space-y-4">

    <div class="flex items-start max-md:flex-col gap-4">
        <div class="flex-1">
            <flux:heading sixe="xl" level="1">{{ $header }}</flux:heading>
            <flux:subheading size="lg" class="mb-4">{{ $subHeader }}</flux:subheading>
        </div>

        <div class="w-50">
            <flux:input wire:model.live.debounce.300ms="search" wire:island="list" type="text" icon="magnifying-glass" placeholder="filtre aqui" />
        </div>

        <flux:button variant="primary" wire:click="$dispatch('{{ $createActionEventName }}')">
            {{ $createButtonLabel }}
        </flux:button>
    </div>
    <flux:separator variant="subtle" />
    <div class="overflow-x-auto">
        {{ $slot }}
    </div>
</div>