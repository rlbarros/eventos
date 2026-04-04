<flux:modal name="{{ $modalName }}" wire:close="handleModalCloseEvent" class="md:w-350">
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

            @if ($submitButtonVisible == '1')
            <flux:button type="submit" variant="primary" :disabled="$submitDisabled" color="navy">
                {{ $submitButtonLabel }}
            </flux:button>
            @endif
        </div>
    </form>
</flux:modal>