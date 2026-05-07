<?php

namespace App\Models;

use App\Traits\WithNameDescriptor;
use App\Utils\DescriptorUtil;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Person extends GenericModel
{
    use WithNameDescriptor;

    protected $table = 'persons';

    protected $fillable = [
        'church_id',
        'name',
        'birth_date',
        'phone',
        'avatar',
        'father_id',
        'mother_id',
        'spouse_id',
        'function',
    ];

    public static function modelName(): string
    {
        return  "Pessoa";
    }

    public function descriptor(): string
    {

        $name = $this->name ?? null;
        $churchName = $this->church->name ?? null;

        return DescriptorUtil::describe($name, $churchName);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    public function father(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'father_id');
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'mother_id');
    }

    public function spouse(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'spouse_id');
    }
}
