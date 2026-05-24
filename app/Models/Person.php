<?php

namespace App\Models;

use App\Traits\WithNameDescriptor;
use App\Utils\DescriptorUtil;
use Carbon\Carbon;
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
        'cpf',
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

    public function age_at_date(string $date): ?int
    {
        if (empty($this->birth_date) || empty($date)) {
            return null;
        }

        $birthDate = Carbon::parse($this->birth_date);
        $comparisonDate = Carbon::parse($date);

        return $birthDate->diffInYears($comparisonDate);
    }

    public function hasDependencies()
    {
        $hasAllocations = EventParticipantAllocation::where('person_id', $this->id)->exists();
        $hasServices = EventServiceParticipantConsumption::where('person_id', $this->id)->exists();
        $hasTrips = EventTripParticipant::where('person_id', $this->id)->exists();
        return $hasAllocations || $hasServices || $hasTrips;
    }
}
