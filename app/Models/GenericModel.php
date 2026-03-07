<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class GenericModel extends Model
{
    public abstract static function modelName(): string;
    public abstract function descriptor(): string;

    // public static function getTableName()
    // {
    //     return with(new static)->getTable();
    // }

    // public static function getTimestamps()
    // {
    //     return with(new static)->timestamps();
    // }
}
