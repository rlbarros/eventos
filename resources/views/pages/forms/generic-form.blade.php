<?php

use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {

    public $modalArray;
    public $modalName;
    public $formTitle;
    public $submitButtonVisible;
    public $submitButtonLabel;

    public function mount()
    {
        $this->modalName = $this->modalArray['modalName'] ?? null;
        $this->formTitle = $this->modalArray['formTitle'] ?? null;
        $this->submitButtonVisible = $this->modalArray['submitButtonVisible'] ?? null;
        $this->submitButtonLabel = $this->modalArray['submitButtonLabel'] ?? null;
    }

    #[Reactive]
    public $submitDisabled = true;
}
?>

<flux:modal :name="$modalName" wire:close="handleModalCloseEvent" class="md:w-350">
    <form class="space-y-8" wire:submit.prevent="save">

        <div class="space-y-2">
            <flux:heading size="lg">
                {{ $formTitle }}
            </flux:heading>
        </div>

        <div class="space-y-6">
            {{ $slot }}
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <flux:modal.close>
                <flux:button variant="subtle">
                    Fechar
                </flux:button>
            </flux:modal.close>

            @island
            <flux:button type="submit" variant="primary" :wire:show="!$submitButtonVisible" :disabled="$submitDisabled" color="navy">
                {{ $submitButtonLabel }}
            </flux:button>
            @endisland
        </div>
    </form>
</flux:modal>