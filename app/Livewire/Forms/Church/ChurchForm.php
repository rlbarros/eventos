<?php

namespace App\Livewire\Forms\Church;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\Church;
use App\Models\GenericModel;
use App\Services\HostJurisdiction;

class ChurchForm extends GenericForm
{

    public $name = '';
    public $state_id = '';
    public $city_id = '';
    // igrejas.id na administração (catálogo administration_churches, via data-sync)
    public $administration_system_id = null;

    public function fixedRules(): array
    {
        return [
            'state_id' => 'required|integer|exists:states,id',
            'city_id' => 'required|integer|exists:cities,id',
            'administration_system_id' => ['nullable', 'integer', 'exists:administration_churches,id', function ($attribute, $value, $fail) {
                // o vínculo decide a jurisdição dos eventos da igreja: anfitrião de igreja ou de
                // superintendência não mexe nele (só nacional e contas antigas)
                if (! $this->canEditAdministrationLink() && (int) $value !== (int) ($this->model->getOriginal('administration_system_id') ?? 0)) {
                    $fail('Só um anfitrião nacional pode ligar a igreja à da administração.');
                }
            }],
        ];
    }

    public function canEditAdministrationLink(): bool
    {
        $jurisdiction = HostJurisdiction::for(auth()->user());

        return $jurisdiction->isLegacy() || $jurisdiction->isNational();
    }

    public function insertRules(): array
    {
        return [
            'name' => 'unique:churches,name'
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

        /** @var Church */
        $church = $model;

        if (empty($church) || empty($church->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $church->id;
        $this->name = $church->name;
        $this->state_id = $church->state_id;
        $this->city_id = $church->city_id;
        $this->administration_system_id = $church->administration_system_id;
    }
}
