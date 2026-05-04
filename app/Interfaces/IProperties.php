<?php

namespace App\Interfaces;

interface IProperties
{
    public function model();
    public function generMale(): bool;
    public function routeName(): string;
    public function routeParameters(): array;
    public function customOrderingColumn(): string;
    public function customWhereIndex(): array;
}
