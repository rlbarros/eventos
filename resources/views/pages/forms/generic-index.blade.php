<?php

use Livewire\Component;

new class extends Component {

    public $indexArray;
}
?>

<x-pages::forms.layout>
    <livewire:pages::forms.generic-list :indexArray="$indexArray">
        {{ $slot }}
    </livewire:pages::forms.generic-list>
</x-pages::forms.layout>