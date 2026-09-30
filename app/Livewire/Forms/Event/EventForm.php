<?php

namespace App\Livewire\Forms\Event;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\Event;
use App\Models\GenericModel;
use App\Services\HostJurisdiction;

class EventForm extends GenericForm
{


    public string $name = '';
    public string $contact_name = '';
    public string $contact_phone = '';
    public string $pix_key = '';
    public string $pix_beneficiary = '';
    public string $scope = 'igreja';
    public string $start_date = '';
    public string $end_date = '';
    public int $church_id = 0;
    public int $event_site_id = 0;
    public int|null $children_age = 0;
    public function fixedRules(): array
    {
        return [
            'contact_name' => 'nullable|string|max:200',
            'contact_phone' => 'nullable|string|max:20',
            'pix_key' => 'nullable|string|max:140',
            'pix_beneficiary' => 'nullable|string|max:200',
            'scope' => 'required|in:nacional,superintendencia,igreja',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'church_id' => ['required', 'integer', 'exists:churches,id', function ($attribute, $value, $fail) {
                // anfitrião só cria/edita eventos na própria jurisdição (abrangência + igreja)
                if (! HostJurisdiction::for(auth()->user())->canManage($this->scope, (int) $value)) {
                    $fail(HostJurisdiction::OUT_OF_JURISDICTION);
                }
            }],
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
        $this->contact_name = $Event->contact_name ?? '';
        $this->contact_phone = $Event->contact_phone ?? '';
        $this->pix_key = $Event->pix_key ?? '';
        $this->pix_beneficiary = $Event->pix_beneficiary ?? '';
        $this->scope = $Event->scope;
        $this->start_date = $Event->start_date;
        $this->end_date = $Event->end_date;
        $this->church_id = $Event->church_id;
        $this->event_site_id = $Event->event_site_id;
        $this->children_age = $Event->children_age;
    }
}
