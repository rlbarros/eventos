<?php

namespace App\Traits;

use App\Utils\DescriptorUtil;

trait WithNameDescriptor
{
    public function descriptor(): string
    {
        $name = $this->name ?? null;

        return DescriptorUtil::describe($name, null);
    }
}
