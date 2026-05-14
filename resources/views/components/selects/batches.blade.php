<?php

use App\Traits\Forms\Event\Batch\WithEventBatchProperties;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    use WithEventBatchProperties;

    public object $form;

    public bool $readonly;

    public array $selectArray = [
        'label' => 'Lote',
        'pickLabel' => 'Selecione um lote ...',
        'formProperty' => 'event_batch_id',
        'selectChangeMethod' => 'eventBatchSelected',
    ];

    public function eventBatchSelected()
    {
        $this->dispatch('event-batch-selected', $this->form->event_batch_id);
    }

    #[On('event-batch-injected')]
    public function eventBatchInjected(int $id)
    {
        $this->form->event_batch_id = $id;
    }
};

?>


<livewire:selects.generic-select :selectArray="$this->selectArray" :form="$this->form"
    :readonly="$this->readonly" :model="$this->model()" :customWhereIndex="$this->customWhereIndex()"
    :customOrderingColumn="$this->customOrderingColumn()" />