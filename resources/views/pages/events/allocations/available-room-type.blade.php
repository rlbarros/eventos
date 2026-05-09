<?php


use Livewire\Component;

new class extends Component
{
    public object $roomType;
    public array $rooms;

    public function availableBeds()
    {
        $availableBeds = 0;
        foreach ($this->rooms as $room) {
            $availableBeds += $room['availableBeds'];
        }
        return $availableBeds;
    }

    public function totalBeds()
    {
        $totalBeds = 0;
        foreach ($this->rooms as $room) {
            $totalBeds += $room['totalBeds'];
        }
        return $totalBeds;
    }

    public function occupedBeds()
    {
        $occupedBeds = 0;
        foreach ($this->rooms as $room) {
            $occupedBeds += $room['occupedBeds'];
        }
        return $occupedBeds;
    }
};
?>

<x-mary-collapse separator>
    <x-slot:heading>
        {{ $roomType['name'] }}
        <flux:badge color="lime" rounded class="ml-4">{{$this->availableBeds()}}</flux:badge>
    </x-slot:heading>
    <x-slot:content>
        @foreach($rooms as $room)

        @if($loop->iteration > 1)
        <flux:separator variant="subtle" class="m-4" style="width: 365px!important;" />
        @endif

        <flux:checkbox.group label="{{$room['room']->name}}">
            @if(count($room['allocations']) > 1)
            <flux:checkbox.all label="Todos">
                <flux:checkbox.indicator />
                <flux:badge color="lime" rounded class="ml-4">{{ $room['availableBeds']}}</flux:badge>
            </flux:checkbox.all>
            @endif
            @foreach($room['allocations'] as $participant)
            <livewire:pages::events.allocations.event-participant :participant="$participant" :wire:key="$participant['id']" />
            @endforeach
        </flux:checkbox.group>
        @endforeach
    </x-slot:content>
</x-mary-collapse>