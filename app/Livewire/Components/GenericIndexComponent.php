<?php

namespace App\Livewire\Components;

use App\Interfaces\IProperties;
use App\Models\GenericModel;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

abstract class GenericIndexComponent extends Component implements IProperties
{

    use WithPagination;

    public bool $withPagination = true;

    abstract public function model();
    abstract public function generMale(): bool;
    abstract public function routeName(): string;
    abstract public function routeParameters(): array;

    #[Url(history: true)]
    public string $search = '';

    public bool $searchVisible = true;

    public function modelName(): string
    {
        return $this->model()::modelName();
    }

    public function columnFilter(): string
    {
        return 'name';
    }

    public function whereHasTable(): string
    {
        return '';
    }

    protected $listeners = ['search-updated' => '$refresh'];


    #[Computed]
    public function index()
    {
        $query = $this->model()->query();
        foreach ($this->customWhereIndex() as $whereArray) {
            [$column, $operator, $value] = $whereArray;
            $query->where($column, $operator, $value);
        }

        if (!empty($this->search) && $this->searchVisible) {
            if (empty($this->whereHasTable())) {
                $query->whereRaw('LOWER(' . $this->columnFilter() . ') LIKE \'%' . strtolower($this->search) . '%\'');
            } else {
                $query->whereHas($this->whereHasTable(), function ($whereHasQuery) {
                    $whereHasQuery->whereRaw('LOWER(' . $this->columnFilter() . ') LIKE \'%' . strtolower($this->search) . '%\'');
                });
            }
        }

        if (!empty($this->customOrderingColumn())) {
            $query->latest($this->customOrderingColumn());
        }

        if ($this->withPagination) {
            return $query->paginate(10);
        } else {
            return $query->get();
        }
    }

    public function exclusionSuccessMessage(GenericModel $model): string
    {
        $article = $this->generMale() ? 'o' : 'a';
        return $this->modelName() . ' ' . $model->descriptor() . ' excluíd' . $article . ' com sucesso!';
    }


    public function delete(int $id)
    {
        try {
            $model = $this->model()->findOrFail($id);
            $model->delete();
            Toaster::success($this->exclusionSuccessMessage($model));
            Flux::modal('dialogs.delete-confirmation')->close();
            $this->redirectRoute($this->routeName(), $this->routeParameters());
        } catch (\Exception $e) {
            Toaster::warning('erro ' . $e->getMessage() . 'ao  apagar  ' . $this->modelName() . ' ' . $this->model()->descriptor());
        }
    }
};
