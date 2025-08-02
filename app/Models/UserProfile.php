<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'username',
        'business_name',
        'profile_slug',
        'avatar_url',
        'bio',
        'industry',
        'location',
        'total_sessions',
        'total_events_tracked',
        'conversion_rate',
        'popular_topics',
        'response_quality_score',
        'reviews_count',
        'average_rating',
        'featured_testimonials',
        'share_qr_code_url',
        'website_url',
        'social_links',
        'contact_email',
        'is_public',
        'last_active_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'popular_topics' => 'array',
        'featured_testimonials' => 'array',
        'social_links' => 'array',
        'conversion_rate' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'response_quality_score' => 'integer',
        'total_sessions' => 'integer',
        'total_events_tracked' => 'integer',
        'reviews_count' => 'integer',
        'is_public' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique profile slug
     */
    public static function generateProfileSlug(string $businessName): string
    {
        $slug = \Str::slug($businessName);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('profile_slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate QR code URL for the profile
     */
    public function generateQrCodeUrl(): string
    {
        $profileUrl = route('profile.public', ['slug' => $this->profile_slug]);
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($profileUrl);
    }

    /**
     * Update last active timestamp
     */
    public function updateLastActive(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    /**
     * Get formatted conversion rate
     */
    public function getFormattedConversionRateAttribute(): string
    {
        return number_format($this->conversion_rate, 1) . '%';
    }

    /**
     * Get formatted average rating
     */
    public function getFormattedAverageRatingAttribute(): string
    {
        return number_format($this->average_rating, 1) . '/5';
    }

    /**
     * Get profile URL
     */
    public function getProfileUrlAttribute(): string
    {
        return route('profile.public', ['slug' => $this->profile_slug]);
    }

    /**
     * Check if profile is active (last active within 30 days)
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->last_active_at && $this->last_active_at->diffInDays(now()) <= 30;
    }
}
