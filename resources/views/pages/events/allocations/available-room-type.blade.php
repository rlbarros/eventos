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
        <flux:callout inline>
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start" style="font-size: 1rem; font-weight:bold">{{ $roomType['name'] }} </flux:callout.heading>
            <x-slot name="controls" class="mt-1">
                <flux:badge color="indigo" size="xs" rounded>{{$this->totalBeds()}}</flux:badge>
                <flux:badge color="green" rounded size="xs">{{$this->availableBeds()}}</flux:badge>
                <flux:badge color="red" rounded size="xs">{{$this->occupedBeds()}}</flux:badge>
            </x-slot>
        </flux:callout>
    </x-slot:heading>
    <x-slot:content>
        @foreach($rooms as $room)

        @if($loop->iteration > 1)
        <flux:separator variant="subtle" class="m-4" style="width: 365px!important;" />
        @endif

        <flux:field>
            <flux:label class="flex gap-2 @max-md:flex-col items-start">
                <flux:text style="font-size: 1rem; font-weight:bold" class="mt-1">{{$room['room']->name}}</flux:text>
                <flux:badge color="indigo" size="xs" rounded>{{$room['totalBeds']}}</flux:badge>
                <flux:badge color="green" rounded size="xs">{{$room['availableBeds']}}</flux:badge>
                <flux:badge color="red" rounded size="xs">{{$room['occupedBeds']}}</flux:badge>
            </flux:label>

            <flux:checkbox.group>
                @if(count($room['allocations']) > 1)
                <flux:checkbox.all label="Todos">
                    <flux:checkbox.indicator />
                </flux:checkbox.all>
                @endif
                @foreach($room['allocations'] as $participant)
                <livewire:pages::events.allocations.event-participant :participant="$participant" :wire:key="$participant['id']" />
                @endforeach
            </flux:checkbox.group>
        </flux:field>
        @endforeach
    </x-slot:content>
</x-mary-collapse>