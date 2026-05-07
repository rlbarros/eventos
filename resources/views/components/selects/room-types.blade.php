<?php


use App\Traits\Forms\EventSite\WithEventSiteRoomTypeProperties;
use Livewire\Component;

new class extends Component {

    use WithEventSiteRoomTypeProperties;

    public object $form;

    public bool $readonly;

    public array $selectArray = [
        'label' => 'Tipo de Quarto',
        'pickLabel' => 'Selecione um tipo de quarto ...',
        'formProperty' => 'event_site_room_type_id',
        'selectChangeMethod' => 'roomTypeSelected',
    ];

    public function roomTypeSelected()
    {
        $this->dispatch('event-site-room-type-selected', $this->form->event_site_room_type_id);
    }
};

?>


<livewire:selects.generic-select :selectArray="$this->selectArray" :form="$this->form"
    :readonly="$this->readonly" :model="$this->model()" :customWhereIndex="$this->customWhereIndex()"
    :customOrderingColumn="$this->customOrderingColumn()" />