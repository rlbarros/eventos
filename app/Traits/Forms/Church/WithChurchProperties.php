<?php

namespace App\Traits\Forms\Church;

use App\Models\Church;

trait WithChurchProperties
{

    public function model()
    {
        return new Church();
    }

    public function routeName(): string
    {
        return 'churches';
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
