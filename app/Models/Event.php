<?php

namespace App\Models;

use App\Models\Traits\LoadRelationsAndCounts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory, LoadRelationsAndCounts;

    protected $fillable = ['name', 'description', 'start_time', 'end_time', 'user_id'];

    protected $casts = [
        'start_time' => 'datetime:Y-m-d H:i',
        'end_time' => 'datetime:Y-m-d H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function scopeSortByStartTime(Builder $builder): Builder
    {
        return $builder->orderBy('start_time', 'desc');
    }
}
