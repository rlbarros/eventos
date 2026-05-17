<?php

use App\Traits\Forms\Event\Service\WithEventServiceProperties;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    use WithEventServiceProperties;

    public object $form;

    public bool $readonly;

    public array $selectArray = [
        'label' => 'Serviço',
        'pickLabel' => 'Selecione um serviço ...',
        'formProperty' => 'event_service_id',
        'selectChangeMethod' => 'eventServiceSelected',
    ];

    public function eventServiceSelected()
    {
        $this->dispatch('event-service-selected', $this->form->event_service_id);
    }

    #[On('event-service-injected')]
    public function eventServiceInjected(int $id)
    {
        $this->form->event_service_id = $id;
    }
};

?>


<livewire:selects.generic-select :selectArray="$this->selectArray" :form="$this->form"
    :readonly="$this->readonly" :model="$this->model()" :customWhereIndex="$this->customWhereIndex()"
    :customOrderingColumn="$this->customOrderingColumn()" />