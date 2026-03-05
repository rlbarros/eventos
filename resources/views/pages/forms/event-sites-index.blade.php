<?php

use Livewire\Component;
use Livewire\Attributes\Title;


new #[Title('Locais de Evento')] class extends Component {
    //
}; ?>


<x-pages::forms.layout>
    <div class="w-full mx-auto space-y-4">
        <div class="flex items-start max-md:flex-col">
            <div class="flex-1">
                <flux:heading sixe="xl" level="1">{{ __('Locais de Evento') }}</flux:heading>
                <flux:subheading size="lg" class="mb-4">{{ __('cadastre as chácaras, estâncias ou quaisquer outros
                    locais de receplção onde ocorrem os eventos da IEA.') }}</flux:subheading>
            </div>
            <flux:modal.trigger name="create-event-site">
                <flux:button variant="primary">
                    Criar Local de Evento
                </flux:button>
            </flux:modal.trigger>

            <livewire:pages::forms.event-sites-post />
        </div>
    </div>
    <flux:separator variant="subtle" />
</x-pages::forms.layout>