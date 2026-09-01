<?php

namespace App\Models;

use App\Traits\WithNameDescriptor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends GenericModel
{
    use WithNameDescriptor;

    protected $table = 'events';

    protected $fillable = [
        'name',
        'scope',
        'start_date',
        'end_date',
        'church_id',
        'event_site_id',
        'children_age',
        'owner_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->owner_id) && auth()->check()) {
                $event->owner_id = auth()->id();
            }
        });
    }

    public static function modelName(): string
    {
        return  "Evento";
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    public function event_site(): BelongsTo
    {
        return $this->belongsTo(EventSite::class, 'event_site_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function allowedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'events_users', 'event_id', 'user_id');
    }

    public function isOwner(?User $user): bool
    {
        return $user && $this->owner_id === $user->id;
    }

    public function isAllowedUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->isOwner($user) || $this->allowedUsers()->whereKey($user->id)->exists();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhereHas('allowedUsers', fn (Builder $query) => $query->whereKey($user->id));
        });
    }

    public function hasDependencies()
    {
        $hasParticipants = EventParticipantAllocation::where('event_id', $this->id)->exists();
        $hasBatches = EventBatch::where('event_id', $this->id)->exists();
        $hasFees = EventFee::where('event_id', $this->id)->exists();
        $hasServices = EventService::where('event_id', $this->id)->exists();
        $hasTrips = EventTrip::where('event_id', $this->id)->exists();

        return $hasParticipants || $hasBatches || $hasFees || $hasServices || $hasTrips;
    }
}
