<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    protected $fillable = [
        'campsite_id',
        'name',
        'email',
        'language',
        'booking_ref',
        'checkin_date',
        'checkout_date',
        'pitch_number',
    ];

    protected $casts = [
        'checkin_date'  => 'date',
        'checkout_date' => 'date',
    ];

    public function campsite(): BelongsTo
    {
        return $this->belongsTo(Campsite::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}