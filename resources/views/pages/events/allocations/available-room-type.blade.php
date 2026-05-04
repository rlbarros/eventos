<?php


use Livewire\Component;

new class extends Component
{
    public object $roomType;
    public array $rooms;

    public function totalAvailableBeds()
    {
        $totalBeds = 0;
        foreach ($this->rooms as $room) {
            $totalBeds += $room['availableBeds'];
        }
        return $totalBeds;
    }
};
?>

<x-mary-collapse separator>
    <x-slot:heading>
        {{ $roomType['name'] }} <x-badge value="{{$this->totalAvailableBeds()}}" class="badge-primary" />
    </x-slot:heading>
    <x-slot:content>
        @foreach($rooms as $room)
        <x-mary-collapse separator>
            <x-slot:heading>
                {{ $room['room']->name }}"<x-badge value="{{$room['availableBeds']}}" class="badge-primary badge-soft" />
            </x-slot:heading>
            <x-slot:content>
                <div wire:sort:group="participants">

                    @foreach($room['allocations'] as $allocation)
                    <livewire:pages::events.allocations.event-participant :person="$allocation->person" :wire:key="$allocation->id" />
                    @endforeach
                </div>
            </x-slot:content>
        </x-mary-collapse>
        @endforeach
    </x-slot:content>
</x-mary-collapse>