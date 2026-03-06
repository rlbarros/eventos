<?php

namespace App\Livewire\Forms\Forms;

use App\Models\EventSite;
use Livewire\Form;

class EventSiteForm extends Form
{
    public $name = '';
    public $phone = '';
    public $zip_code = '';
    public $state_id = 12;
    public $city_id = 53;
    public $address = '';
    public $number = '';
    public $complement = '';
    public $neighborhood = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|max:200|unique:event_sites,name',
            'phone' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|min:9|max:9',
            'state_id' => 'required|integer|exists:states,id',
            'city_id' => 'required|integer|exists:cities,id',
            'address' => 'required|string|min:3|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:191',
            'neighborhood' => 'nullable|string|max:200'
        ];
    }

    public function store()
    {
        $this->validate();
        EventSite::create(
            $this->only(
                'name',
                'phone',
                'zip_code',
                'state_id',
                'city_id',
                'address',
                'number',
                'complement'
            )
        );
        $this->reset();
    }
}
