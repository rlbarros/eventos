<?php

use App\Models\EventService;
use App\Models\EventServiceParticipantConsumption;
use App\Utils\CurrencyUtil;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {

    public int $eventId;
    public int $serviceId;

    public object $service;

    public Collection $participants;

    public string $totalInServices;
    public string $totalPayed;
    public string $balance;

    use WithPagination;

    public function mount()
    {
        $this->service = EventService::where('id', '=', $this->serviceId)->get()->first();

        $totalParticipantsPayed = EventServiceParticipantConsumption::where('event_service_id', $this->serviceId)
            ->whereNotNull('amount')
            ->sum('amount');

        $this->participants = EventServiceParticipantConsumption::select(
            DB::raw('MIN(id) as id'),
            'event_service_id',
            'person_id'
        )
            ->where('event_service_id', $this->serviceId)
            ->groupBy('event_service_id', 'person_id')
            ->get();
        $this->totalInServices = (count($this->participants) * $this->service->fee);
        $balance = $this->totalInServices - $totalParticipantsPayed;

        $this->totalInServices = CurrencyUtil::formatCurrencyToBr($this->totalInServices);
        $this->balance = CurrencyUtil::formatCurrencyToBr($balance);
        $this->totalPayed = CurrencyUtil::formatCurrencyToBr($totalParticipantsPayed);
    }
};

?>

<div class="w-full mx-auto space-y-4">
    <div class="flex items-start max-md:flex-col">
        <div class="flex-1">
            <flux:callout inline class="mb-4">
                <flux:callout.heading>
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item icon="calendar" href="{{ route('events') }}">Eventos </flux:breadcrumbs.item>
                        <flux:breadcrumbs.item separator="slash" href="{{ route('event-detail', ['eventId' => $this->eventId]) }}" separator="slash">Evento {{ $this->eventId }}</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item separator="slash">Serviço {{ $this->serviceId }}</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                </flux:callout.heading>
            </flux:callout>

            <flux:callout inline class="mb-4" style="max-height: 50px;">
                <flux:callout.heading>
                    <div class="flex flex-row" style="column-gap: 1rem; padding-bottom: 0px!important;padding-top: 10px!important;">
                        <flux:heading size="sm" style="font-size:1.1rem;">{{ $this->service->descriptor()  }}</flux:heading>
                    </div>
                </flux:callout.heading>
            </flux:callout>
            <flux:callout inline>
                <flux:callout.heading>
                    <flux:heading size="sm">Total Requisitado: R$ {{ $this->totalInServices }}</flux:heading>
                    <flux:heading size="sm">Total Pago: R$ {{ $this->totalPayed }}</flux:heading>
                    <flux:heading size="sm">Saldo Devedor: R$ {{ $this->balance }}</flux:heading>
                </flux:callout.heading>
            </flux:callout>
        </div>
    </div>
    <flux:separator variant="subtle" />

    <livewire:pages::events.services.participants.participants-index
        :eventId="$this->eventId"
        :serviceId="$this->serviceId"
        :participants="$this->participants" />

</div>