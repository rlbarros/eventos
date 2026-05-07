<?php

namespace App\Traits;

use App\Utils\DescriptorUtil;

trait WithNameDescriptor
{
    public function descriptor(): string
    {
        $id = $this->id ?? null;
        $name = $this->name ?? null;

        return DescriptorUtil::describe($name, null);
    }
}
