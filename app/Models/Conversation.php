<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $fillable = [
        'campsite_id',
        'guest_id',
        'session_id',
        'detected_language',
        'status',
        'message_count',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Conversation $conversation) {
            if (empty($conversation->session_id)) {
                $conversation->session_id = (string) Str::uuid();
            }
        });
    }

    public function campsite(): BelongsTo
    {
        return $this->belongsTo(Campsite::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function getRecentMessagesForAI(int $limit = 10): array
    {
        return $this->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn(Message $msg) => [
                'role'    => $msg->role,
                'content' => $msg->content,
            ])
            ->values()
            ->toArray();
    }

    public function incrementMessageCount(): void
    {
        $this->increment('message_count');
        $this->update(['last_activity_at' => now()]);
    }
}