<?php


use Livewire\Component;

new class extends Component
{
    public $person;
};
?>


<x-mary-card body-class="card bg-base-100 rounded-lg p-5 !p-3 cursor-pointer hover:bg-base-200 border border-base-content/5 shadow">
    <div class="grow null">
        <div class="text-sm">
            <div class="font-bold">{{$person->name}}</div>
            <div class="text-xs text-base-content/60 line-clamp-1">{{$person->church->name}}</div>
        </div>
    </div>
</x-mary-card>