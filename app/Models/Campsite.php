<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campsite extends Model
{
    protected $fillable = [
        'name',
        'description',
        'location',
        'phone',
        'email',
        'languages',
        'checkin_time',
        'checkout_time',
        'is_active',
    ];

    protected $casts = [
        'languages'  => 'array',
        'is_active'  => 'boolean',
    ];

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function activeFaqsForLanguage(string $language): \Illuminate\Database\Eloquent\Collection
    {
        $faqs = $this->faqs()
            ->where('is_active', true)
            ->where('language', $language)
            ->orderBy('sort_order')
            ->get();

        if ($faqs->isEmpty() && $language !== 'en') {
            $faqs = $this->faqs()
                ->where('is_active', true)
                ->where('language', 'en')
                ->orderBy('sort_order')
                ->get();
        }

        return $faqs;
    }
}