<?php

namespace App\Traits\Forms\EventSite;

use App\Models\EventSite;

trait WithEventSiteProperties
{

    public function model()
    {
        return new EventSite();
    }

    public function routeName(): string
    {
        return 'event-sites';
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
