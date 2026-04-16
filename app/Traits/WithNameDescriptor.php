<?php

namespace App\Traits;

trait WithNameDescriptor
{
    public function descriptor(): string
    {
        if (empty($this->id) && empty($this->name)) {
            return '';
        } else if (!empty($this->id) && !empty($this->name)) {
            return $this->id . ' - ' . $this->name;
        } else if (!empty($this->name)) {
            return $this->name;
        } else if (!empty($this->id)) {
            return $this->id;
        } else {
            return '';
        }
    }
}
