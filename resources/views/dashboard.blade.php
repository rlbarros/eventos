<?php

use App\Models\Event;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    public string $selectedEvent;
    public array $events;

    public string $eventSiteName;

    #[Url]
    public string $selectedTab = 'allocations-tab';

    public function mount()
    {
        $this->events = Event::all()->toArray();
        $this->selectedEvent = 0;
        if (count($this->events) > 0) {
            $this->selectedEvent = $this->events[0]['id'];
            $this->eventSiteName();
        }
    }

    #[Computed()]
    public function eventSiteName(): void
    {
        if (empty($this->selectedEvent)) {
            $this->eventSiteName = '';
            return;
        }
        $event = Event::find($this->selectedEvent);
        $eventSite = $event->event_site;
        $this->eventSiteName = $eventSite->name ?? '';
    }
};
?>

<x-pages::forms.layout>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl"
        x-data="{ 
        selectedEvent: @entangle('selectedEvent'), 
        events: @entangle('events')
        }">
        <div class="flex items-center justify-between gap-4">
            <flux:callout inline style="width:100%;padding-top:1em;">
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start" style="font-size: 1rem; font-weight:bold;">
                    <flux:select x-model="selectedEvent" placeholder="Selecione um evento..." style="width:500px;">
                        <template x-for="event in events" :key="event.id">
                            <option :value="event.id" x-text="event.name"></option>
                        </template>
                    </flux:select>

                </flux:callout.heading>
                <x-slot name="controls" class="mt-1">
                    <div class="flex items-center flex-row gap-4">
                        <flux:heading size="lg">local de evento</flux:heading>
                        <flux:field>
                            <flux:input wire:model="eventSiteName" />
                        </flux:field>
                    </div>
                </x-slot>
            </flux:callout>
        </div>
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                <livewire:pages::dashboards.participants-card :eventId="$selectedEvent" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                <livewire:pages::dashboards.fees-card :eventId="$selectedEvent" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                <livewire:pages::dashboards.services-card :eventId="$selectedEvent" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-mary-tabs wire:model="selectedTab">
                <x-mary-tab name="allocations-tab" icon="o-users">
                    <x-slot:label>
                        alocações
                    </x-slot:label>
                    <livewire:pages::dashboards.allocations-index :eventId="$selectedEvent" class="pt=2" />
                </x-mary-tab>
                <x-mary-tab name="services-tab" icon="o-building-office">
                    <x-slot:label>
                        serviços
                    </x-slot:label>
                    <livewire:pages::dashboards.services-index :eventId="$selectedEvent" class="pt=2" />
                </x-mary-tab>
                <x-mary-tab name="trips-tab" icon="o-building-office">
                    <x-slot:label>
                        viagens
                    </x-slot:label>
                    <livewire:pages::dashboards.trips-index :eventId="$selectedEvent" class="pt=2" />
            </x-mary-tabs>

        </div>
    </div>
</x-pages::forms.layout>