<?php

use App\Traits\Forms\Event\Trip\WithEventTripProperties;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    use WithEventTripProperties;

    public object $form;

    public bool $readonly;

    public array $nonList = [];

    public array $selectArray = [
        'label' => 'Viagem',
        'pickLabel' => 'Selecione uma viagem ...',
        'formProperty' => 'event_trip_id',
        'selectChangeMethod' => 'eventTripSelected',
    ];

    public function eventTripSelected()
    {
        $this->dispatch('event-trip-selected', $this->form->event_trip_id);
    }

    #[On('event-trip-injected')]
    public function eventTripInjected(int $id)
    {
        $this->form->event_trip_id = $id;
    }
};

?>

<livewire:selects.generic-select :selectArray="$this->selectArray" :form="$this->form"
    :readonly="$this->readonly" :model="$this->model()" :customWhereIndex="$this->customWhereIndex()"
    :customOrderingColumn="$this->customOrderingColumn()" :nonList="$this->nonList" />