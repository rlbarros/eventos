<?php

namespace App\Livewire;

use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $objectId;
    public string $callbackEvent;
    public string $modelName;
    public string $descriptor;

    #[On('dialogs.delete-confirmation')]
    public function handleEventSiteCreatingRequest(int $objectId, string $modelName, string $descriptor, string $callbackEvent)
    {
        $this->objectId = $objectId;
        $this->modelName = $modelName;
        $this->descriptor = $descriptor;
        $this->callbackEvent = $callbackEvent;
        Flux::modal('dialogs.delete-confirmation')->show();
    }

    public function callbackEvent()
    {
        $this->dispatch($this->callbackEvent, ['id' => $this->objectId]);
    }
}

?>

<div>
    <flux:modal name="dialogs.delete-confirmation" class="md:w-650">
        <div class="pt-4 h-25 mt-5">
            <p>
                <strong>Realmente </strong>deseja apagar o <span>{{$modelName}}</span>
                <span>&nbsp;</span><span>{{$descriptor}}</span>?
            </p>
        </div>
        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <flux:modal.close>
                <flux:button variant="subtle">
                    Cancelar
                </flux:button>

            </flux:modal.close>

            <flux:button variant="primary" wire:click="callBackEvent">
                Confirmar
            </flux:button>

        </div>

    </flux:modal>
</div>