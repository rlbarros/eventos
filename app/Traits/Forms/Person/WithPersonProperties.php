<?php

namespace App\Traits\Forms\Person;

use App\Models\Person;

trait WithPersonProperties
{

    public function model()
    {
        return new Person();
    }

    public function routeName(): string
    {
        return 'persons';
    }

    public function routeParameters(): array
    {
        return [];
    }

    public function generMale(): bool
    {
        return false;
    }
}
