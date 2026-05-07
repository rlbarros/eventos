<?php

use App\Livewire\Components\GenericAutocompleteComponent;
use App\Traits\Forms\Church\WithChurchProperties;
use Livewire\Attributes\On;

new class extends GenericAutocompleteComponent {

    use WithChurchProperties;

    #[On('church-search-term')]
    public function handleChurchSearched()
    {
        $this->loadRecords();
    }
}
?>

<livewire:autocompletes::generic-autocomplete
    :readonly="$readonly"
    label="Igreja"
    :searchTerm="$searchTerm"
    :records="$records"
    :autocompleteArray="[
        'label' => 'Igreja',
        'idField' => 'church_id',
        'searchField' => 'name',
        'searchEvent' => 'church-search-term',
        'selectedEvent' => 'church-selected',
    ]" />