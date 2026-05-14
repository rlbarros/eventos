<?php

namespace App\Livewire\Forms\Event\Fee;

use App\Enum\FeeCategoryEnum;
use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;
use App\Models\EventFee;

class EventFeeForm extends GenericForm
{

    public int $event_id = 0;
    public int $event_site_room_type_id = 0;
    public int $event_batch_id = 0;
    public string $category = '';
    public string $fee = '';


    public function fixedRules(): array
    {
        $categories = array_map(fn(FeeCategoryEnum $category) => $category->value, FeeCategoryEnum::cases());
        return [
            'event_id' => 'required|integer|exists:events,id',
            'event_site_room_type_id' => 'required|integer|exists:event_site_room_types,id',
            'event_batch_id' => 'required|integer|exists:events_batches,id',
            'category' => 'required|in:' . implode(',', $categories),
            'fee' => 'required|numeric|min:0.01|max:999999.99'
        ];
    }

    public function insertRules(): array
    {
        return [];
    }

    public function updateRules(): array
    {
        return [];
    }

    public function setModel(FormModeEnum $formMode, GenericModel $model): void
    {
        $this->formMode = $formMode;
        $this->model = $model;

        /** @var EventFee */
        $eventFee = $model;

        if (empty($eventFee) || empty($eventFee->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventFee->id;
        $this->event_id = $eventFee->event_id;
        $this->event_site_room_type_id = $eventFee->event_site_room_type_id;
        $this->event_batch_id = $eventFee->event_batch_id;
        $this->category = $eventFee->category;
        $this->fee = $eventFee->fee;
    }
}
