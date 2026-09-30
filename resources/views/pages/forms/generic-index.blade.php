<?php

use Livewire\Component;

new class extends Component {

    public array $indexArray;
}
?>

<x-pages::forms.layout>
    <livewire:dialogs::delete-confirmation />
    <livewire:pages::forms.generic-list :indexArray="$indexArray">
        @if($slots->has('extraFilters'))
        <x-slot:extraFilters>
            {{ $slots->get('extraFilters') }}
        </x-slot:extraFilters>
        @endif
        {{ $slot }}
    </livewire:pages::forms.generic-list>
</x-pages::forms.layout>