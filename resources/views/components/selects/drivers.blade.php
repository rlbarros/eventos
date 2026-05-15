<?php

use App\Traits\Forms\Event\Driver\WithEventDriverProperties;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    use WithEventDriverProperties;

    public object $form;

    public bool $readonly;

    public array $selectArray = [
        'label' => 'Motorista',
        'pickLabel' => 'Selecione um motorista ...',
        'formProperty' => 'event_driver_id',
        'selectChangeMethod' => 'eventDriverSelected',
    ];

    public function eventDriverSelected()
    {
        $this->dispatch('event-driver-selected', $this->form->event_driver_id);
    }

    #[On('event-driver-injected')]
    public function eventDriverInjected(int $id)
    {
        $this->form->event_driver_id = $id;
    }
};

?>


<livewire:selects.generic-select :selectArray="$this->selectArray" :form="$this->form"
    :readonly="$this->readonly" :model="$this->model()" :customWhereIndex="$this->customWhereIndex()"
    :customOrderingColumn="$this->customOrderingColumn()" />