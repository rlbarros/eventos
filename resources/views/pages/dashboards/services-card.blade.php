<?php

use App\Models\EventService;
use App\Models\EventParticipantAllocation;
use App\Models\EventParticipantPayment;
use App\Models\EventServiceParticipantConsumption;

new class extends \Livewire\Component
{
    public int $eventId;

    public string $totalServices;
    public string $totalPaidServices;
    public string $totalPendingServices;

    public function mount()
    {
        $eventServiceParticipantConsumptions = EventServiceParticipantConsumption::where('event_id', $this->eventId)->get();
        $this->totalServices = 0;
        $this->totalPaidServices = 0;
        foreach ($eventServiceParticipantConsumptions as $comsuption) {

            $eventService = $comsuption->event_service;
            $this->totalServices += $eventService->fee;

            if (!empty($comsuption->amount)) {
                $this->totalPaidServices += $comsuption->amount;
            }
        }
        $this->totalPendingServices = $this->totalServices - $this->totalPaidServices;
    }
}
?>

<div class="relative flex flex-1 rounded-lg px-6 py-4 bg-zinc-50 dark:bg-zinc-700 flex-col gap-1">
    <flux:callout variant="gray">
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Acumulados de serviços</flux:callout.heading>
    </flux:callout>
    <flux:callout variant="indigo" icon="information-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Serviços</flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="indigo" size="xs" rounded>{{\App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalServices, true)}}</flux:badge>
        </x-slot>
    </flux:callout>
    <flux:callout color="green" icon="check-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Serviços Pagos</flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="green" size="xs" rounded>{{\App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalPaidServices, true)}}</flux:badge>
        </x-slot>
    </flux:callout>
    <flux:callout color="red" icon="exclamation-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Saldo de Serviços a receber</flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="red" size="xs" rounded>{{\App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalPendingServices, true)}}</flux:badge>
        </x-slot>
    </flux:callout>

</div>