<?php

namespace App\Livewire\Forms\Event;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\Event;
use App\Models\GenericModel;

class EventForm extends GenericForm
{


    public string $name = '';
    public string $start_date = '';
    public string $end_date = '';
    public int $church_id = 0;
    public int $event_site_id = 0;
    public int|null $children_age = 0;
    public function fixedRules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'church_id' => 'required|integer|exists:churches,id',
            'event_site_id' => 'required|integer|exists:event_sites,id',
            'children_age' => 'nullable|integer|min:0|max:17',
        ];
    }

    public function insertRules(): array
    {
        return [
            'name' => 'unique:events,name'
        ];
    }

    public function updateRules(): array
    {
        return [
            'name' => 'required|string|min:3|max:200'
        ];
    }

    public function setModel(FormModeEnum $formMode, GenericModel $model): void
    {
        $this->formMode = $formMode;
        $this->model = $model;

        /** @var Event */
        $Event = $model;

        if (empty($Event) || empty($Event->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $Event->id;
        $this->name = $Event->name;
        $this->start_date = $Event->start_date;
        $this->end_date = $Event->end_date;
        $this->church_id = $Event->church_id;
        $this->event_site_id = $Event->event_site_id;
        $this->children_age = $Event->children_age;
    }
}
