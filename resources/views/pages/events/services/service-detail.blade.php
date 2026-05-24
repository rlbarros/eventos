<?php

use App\Models\EventService;
use App\Models\EventServiceParticipantConsumption;
use App\Models\EventServiceParticipantPayment;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
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

    public array $nonList;

    #[Url(history: true)]
    public string $search = '';

    use WithPagination;

    public function mount()
    {
        $this->service = EventService::where('id', '=', $this->serviceId)->get()->first();

        $this->participants = EventServiceParticipantConsumption::where('event_service_id', $this->serviceId)
            ->with('person')
            ->whereHas('person', function ($whereHasQuery) {
                if (empty($this->search)) {
                    return;
                }
                $whereHasQuery->whereRaw('LOWER(name) LIKE \'%' . strtolower($this->search) . '%\'');
            })
            ->get();

        $consumptionsIds = $this->participants->map(function ($item) {
            return $item['id'];
        })->toArray();

        $nonList = $this->participants->map(function ($item) {
            return $item['person_id'];
        })->toArray();

        if (empty($nonList)) {
            $nonList = [];
        }

        $this->nonList = $nonList;

        $totalParticipantsPayed = EventServiceParticipantPayment::whereIn('consumption_id', $consumptionsIds)
            ->sum('amount');


        $this->totalInServices = 0;
        foreach ($this->participants as $participant) {
            $this->totalInServices += $participant->event_service->fee * $participant->quantity;
        }

        $this->totalPayed = $totalParticipantsPayed;
        $this->balance = max(0, $this->totalInServices - $totalParticipantsPayed);
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
                        <flux:breadcrumbs.item separator="slash" href="{{ route('event-detail', ['eventId' => $this->eventId, 'selectedTab' => 'services-tab']) }}" separator="slash">Evento {{ $this->eventId }}</flux:breadcrumbs.item>
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
                    <flux:heading size="sm">Total Requisitado: {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalInServices, true) }}</flux:heading>
                    <flux:heading size="sm">Total Pago: {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($this->totalPayed, true) }}</flux:heading>
                    <flux:heading size="sm">Saldo Devedor: {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($this->balance, true) }}</flux:heading>
                </flux:callout.heading>
            </flux:callout>
        </div>
    </div>
    <flux:separator variant="subtle" />

    <livewire:pages::events.services.participants.participants-index
        :eventId="$this->eventId"
        :serviceId="$this->serviceId"
        :participants="$this->participants"
        :nonList="$this->nonList" />

</div>