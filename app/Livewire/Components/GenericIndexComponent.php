<?php

namespace App\Livewire\Components;

use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;


abstract class GenericIndexComponent extends Component
{

    use WithPagination;

    abstract public function model();
    abstract public function generMale(): bool;
    abstract public function routeName(): string;
    abstract public function routeParameters(): array;

    public function modelName(): string
    {
        return $this->model()::modelName();
    }

    #[Computed]
    public function index()
    {
        return $this->model()::latest()->paginate(10);
    }

    public function exclusionSuccessMessage($model): string
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
