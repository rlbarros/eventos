<?php

namespace App\Traits\Forms\Event;

use App\Models\Event;

trait WithEventProperties
{

    public function model()
    {
        return new Event();
    }

    public function routeName(): string
    {
        return 'events';
    }

    public function routeParameters(): array
    {
        return [];
    }

    public function generMale(): bool
    {
        return true;
    }
}
