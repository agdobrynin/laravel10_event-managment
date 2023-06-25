<?php

namespace App\Models;

use App\Models\Traits\LoadRelationsAndCounts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Attendee extends Model
{
    use HasFactory, LoadRelationsAndCounts;

    protected $fillable = ['user_id', 'event_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeByEventId(Builder $builder, int $eventId): Builder
    {
        return $builder->where('event_id', $eventId)
            ->latest();
    }
}
