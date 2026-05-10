<?php

use Livewire\Component;

new class extends Component
{
    public array $roomType;

    public string $name;
    public array $churches;
    public int $totalParticipants = 0;

    public function mount()
    {
        $this->name = $this->roomType['roomType'] ?? '';
        $this->churches = $this->roomType['churches'] ?? [];
        foreach ($this->churches as $church) {
            $this->totalParticipants += count($church['participants']);
        }
    }
};
?>

<x-mary-collapse separator>
    <x-slot:heading>
        <flux:callout inline>
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start" style="font-size: 1rem; font-weight:bold"> {{ $name }} </flux:callout.heading>
            <x-slot name="controls" class="mt-1">
                <flux:badge color="green" rounded size="xs">{{$totalParticipants}}</flux:badge>
            </x-slot>
        </flux:callout>
    </x-slot:heading>
    <x-slot:content>
        @foreach($churches as $church)
        @if($loop->iteration > 1)
        <flux:separator class="mt-4 mb-4 ml-0 mr-0" style="width: 395px!important;" />
        @endif
        <livewire:pages::events.allocations.unnalocated-church :church="$church" />
        @endforeach
    </x-slot:content>
</x-mary-collapse>