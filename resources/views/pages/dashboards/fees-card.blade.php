<?php

use App\Models\EventFee;
use App\Models\EventParticipantAllocation;
use App\Models\EventParticipantPayment;

new class extends \Livewire\Component
{
    public int $eventId;

    public string $totalFees;
    public string $totalPaidFees;
    public string $totalPendingFees;

    public function mount()
    {
        $eventAllocations = EventParticipantAllocation::where('event_id', $this->eventId)->get();
        $eventFees = EventFee::where('event_id', $this->eventId)->get();
        $this->totalFees = 0;
        $this->totalPaidFees = 0;
        foreach ($eventAllocations as $allocation) {

            $eventSiteRoomType = $allocation->event_site_room_type;
            $paymetnsOfAllocation = EventParticipantPayment::where('event_id', $this->eventId)->where('person_id', $allocation->person_id)->get();
            $addedFee = false;
            if ($paymetnsOfAllocation->count() > 0) {
                foreach ($paymetnsOfAllocation as $payment) {
                    $this->totalPaidFees += $payment->amount;
                    if (!$addedFee) {
                        $eventFeeForRoomType = $eventFees->where('event_site_room_type_id', $eventSiteRoomType->id)->sortByDesc('batch')->first();
                        if ($eventFeeForRoomType) {
                            $this->totalFees += $eventFeeForRoomType->fee;
                            $addedFee = true;
                        }
                    }
                }
                $this->totalPaidFees += $paymetnsOfAllocation->sum('amount');
            } else {
                $eventFeeForRoomType = $eventFees->where('event_site_room_type_id', $eventSiteRoomType->id)->sortByDesc('batch')->first();
                if ($eventFeeForRoomType) {
                    $this->totalFees += $eventFeeForRoomType->fee;
                }
            }
        }
        $this->totalPendingFees = $this->totalFees - $this->totalPaidFees;
    }
}
?>

<div class="relative flex flex-1 rounded-lg px-6 py-4 bg-zinc-50 dark:bg-zinc-700 flex-col gap-1">
    <flux:callout variant="gray">
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Acumulados de inscrições</flux:callout.heading>
    </flux:callout>
    <flux:callout variant="indigo" icon="information-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Inscrições</flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="indigo" size="xs" rounded>{{\App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalFees, true)}}</flux:badge>
        </x-slot>
    </flux:callout>
    <flux:callout color="green" icon="check-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Inscrições Pagas</flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="green" size="xs" rounded>{{\App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalPaidFees, true)}}</flux:badge>
        </x-slot>
    </flux:callout>
    <flux:callout color="red" icon="exclamation-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Saldo de Inscrições a receber</flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="red" size="xs" rounded>{{\App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalPendingFees, true)}}</flux:badge>
        </x-slot>
    </flux:callout>

</div>