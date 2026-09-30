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
        <livewire:slot name="extraFilters">
            {{ $slots->get('extraFilters') }}
        </livewire:slot>
        @endif
        {{ $slot }}
    </livewire:pages::forms.generic-list>
</x-pages::forms.layout>