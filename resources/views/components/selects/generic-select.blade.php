<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public string $label = '';
    public array $selectArray = [];
    public string $pickLabel = '';
    public array $customWhereIndex = [];
    public string $customOrderingColumn = '';
    public string $formProperty = '';

    public Collection $records;
    public bool $recordsLoading = true;

    public object $model;
    public bool $readonly;
    public object $form;
    public string $selectChangeMethod;

    public bool $withPagination = false;

    public function mount()
    {
        $this->label = $this->selectArray['label'] ?? '';
        $this->pickLabel = $this->selectArray['pickLabel'] ?? '';
        $this->formProperty = $this->selectArray['formProperty'] ?? '';
        $this->selectChangeMethod = $this->selectArray['selectChangeMethod'] ?? '';
        $this->records = new Collection();
        $this->loadRecords();
    }

    public function customWhereIndex(): array
    {
        return $this->customWhereIndex;
    }

    public function customOrderingColumn(): string
    {
        return $this->customOrderingColumn;
    }

    public function loadRecords()
    {
        $this->recordsLoading = true;
        try {
            $query = $this->model->query();
            foreach ($this->customWhereIndex() as $whereArray) {
                [$column, $operator, $value] = $whereArray;
                $query->where($column, $operator, $value);
            }
            if (!empty($this->customOrderingColumn())) {
                $this->records = $query->latest($this->customOrderingColumn())->get();
            } else {
                $this->records = $query->latest()->get();
            }
        } catch (\Exception $e) {
            Toaster::warning('não foi possível consultar informações de ' . strtolower($this->label) . ' para o evento. Tente novamente mais tarde.');
            Log::error('error consulting ' . strtolower($this->label) . ' ' . $e->getMessage(), $e->getTrace());
        } finally {
            $this->recordsLoading = false;
        }
    }
}

?>

<flux:field class="w-full">
    <flux:label>{{$label}}</flux:label>
    @if ($this->recordsLoading)
    <flux:input readonly color="zinc">
        <x-slot name="iconTrailing">
            <svg class="mr-3 size-5 animate-spin text-shadow-blue-800" viewBox="0 0 24 24">
            </svg>
        </x-slot>
    </flux:input>
    @else
    <flux:select wire:model.live="form.{{$this->formProperty}}" wire:change='{{$this->selectChangeMethod}}' required :disabled="$readonly">
        <flux:select.option value="0">{{$pickLabel}}</flux:select.option>
        @foreach ($records as $record)
        <flux:select.option :wire:key="$record->id" :value="$record->id">{{ $record->descriptor() }}</flux:select.option>
        @endforeach
    </flux:select>
    @endif

</flux:field>