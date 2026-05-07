<?php

namespace App\Livewire\Components;

use App\Models\GenericModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Json;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

abstract class GenericAutocompleteComponent extends Component
{
    abstract public function model();

    public array $records = [];
    public Collection $collection;

    public GenericModel $selectedRecord;
    public bool $recordsLoading = true;

    public bool $touched = false;
    public bool $readonly;
    public object $form;
    public string $searchTerm = '';
    public string $lastSearchTerm = '';

    public bool $withPagination = false;

    #[Json]
    public function loadRecords(): void
    {
        $searchTerm = $this->searchTerm;
        $this->collection = new Collection();

        // Check if search term contains "|" and take first element if it does
        if (strpos($searchTerm, '|') !== false) {
            $searchTerm = explode('|', $searchTerm)[0];
        }

        if (strlen($searchTerm) < 3) {
            $this->lastSearchTerm = '';
            return;
        }

        $this->touched = true;
        if ($this->collection->isNotEmpty() && $this->collection->hasSole($this->idField, '=', $searchTerm)) {
            $this->selectedRecord = $this->collection->likeClause($this->idField, '=', $searchTerm)->first()->toArray();
            $this->dispatchSelections();
            return;
        }

        $searchTerm = trim(strtolower($searchTerm));

        $isLastSearchTermPrefix = str_starts_with($searchTerm, $this->lastSearchTerm);
        $isCollectionHasAtLeastTwoItems = $this->collection->count() >= 2;
        $isLastSearchTermLowerThanCurrent = strlen($this->lastSearchTerm) < strlen($searchTerm);

        try {
            $query = $this->model()->query();
            foreach ($this->customWhereIndex() as $whereArray) {
                [$column, $operator, $value] = $whereArray;
                $query->likeClause($column, $operator, $value);
            }

            if ($this->touched && $isCollectionHasAtLeastTwoItems && $isLastSearchTermPrefix && $isLastSearchTermLowerThanCurrent) {
                $this->records = $this->collection->filter(function ($record) use ($searchTerm) {
                    if ($this->containsFilter) {
                        return str_contains(strtolower($record[$this->searchField]), $searchTerm);
                    } else {
                        return str_starts_with(strtolower($record[$this->searchField]), $searchTerm);
                    }
                })->toArray();
            } else {

                $likeClause = $searchTerm . '%';
                if ($this->containsFilter) {
                    $likeClause = '%' . $likeClause;
                }


                $query->whereRaw('LOWER(' . $this->searchField . ') LIKE ?', [$likeClause]);

                if (!empty($this->customOrderingColumn())) {
                    $this->collection = $query->latest($this->customOrderingColumn())->get();
                } else {
                    $this->collection = $query->latest()->get();
                }
            }
            $this->lastSearchTerm = $searchTerm;

            if ($this->collection->hasSole()) {
                $this->selectedRecord = $this->collection->first();
                $this->searchTerm = $this->selectedRecord->descriptor();
                $this->dispatchSelections();
            }
            $this->records = $this->collection->toArray();
            $this->dispatch('log-event', ['obj' => $this->records, 'level' => 'info']);
        } catch (\Exception $e) {
            Toaster::warning('não foi possível consultar informações de ' . $this->model()->modelName());
            Log::error('error consulting autocomplete records ' . $e->getMessage(), $e->getTrace());
        }
    }
}
