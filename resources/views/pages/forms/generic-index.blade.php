<?php

use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {

    public $indexArray;
    public $header;
    public $subHeader;
    public $createButtonLabel;
    public $createActionEventName;


    public function mount()
    {
        $this->header = $this->indexArray['header'] ?? null;
        $this->subHeader = $this->indexArray['subHeader'] ?? null;
        $this->createButtonLabel = $this->indexArray['createButtonLabel'] ?? null;
        $this->createActionEventName = $this->indexArray['createActionEventName'] ?? null;
    }
}
?>

<x-pages::forms.layout>
    <div class="w-full mx-auto space-y-4">
        <div class="flex items-start max-md:flex-col">
            <div class="flex-1">
                <flux:heading sixe="xl" level="1">{{ $header }}</flux:heading>
                <flux:subheading size="lg" class="mb-4">{{ $subHeader }}</flux:subheading>
            </div>

            <flux:button variant="primary" wire:click="$dispatch('forms.churchs.church-create')">
                {{ $createButtonLabel }}
            </flux:button>

            <livewire:pages::forms.churches.church-form />
        </div>
    </div>
    <flux:separator variant="subtle" />
    <div class="overflow-x-auto">
        {{ $slot }}
    </div>
    <livewire:dialogs::delete-confirmation />
</x-pages::forms.layout>