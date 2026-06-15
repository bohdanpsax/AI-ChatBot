<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tokens_used',
        'confidence_score',
        'was_fallback',
        'metadata',
    ];

    protected $casts = [
        'was_fallback' => 'boolean',
        'metadata'     => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}