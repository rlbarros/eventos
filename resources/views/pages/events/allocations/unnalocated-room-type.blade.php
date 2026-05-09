<?php

use Livewire\Component;

new class extends Component
{
    public array $roomType;

    public string $name;
    public array $churches;

    public function mount()
    {
        $this->name = $this->roomType['roomType'] ?? '';
        $this->churches = $this->roomType['churches'] ?? [];
    }
};
?>

<x-mary-collapse separator>
    <x-slot:heading>
        {{ $name }}
    </x-slot:heading>
    <x-slot:content>
        @foreach($churches as $church)
        @if($loop->iteration > 1)
        <flux:separator variant="subtle" class="m-4" style="width: 365px!important;" />
        @endif
        <livewire:pages::events.allocations.unnalocated-church :church="$church" />
        @endforeach
    </x-slot:content>
</x-mary-collapse>