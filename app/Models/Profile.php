<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Profile extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'bio',
        'avatar_url',
        'cover_url',
        'posts_count',
        'photos_count',
        'videos_count',
        'likes_count',
        'followers_count',
        'social_links',
        'last_post_at',
        'last_scraped_at',
        'is_verified',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'social_links' => 'array',
        'last_post_at' => 'datetime',
        'last_scraped_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the tags for the profile.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'profile_tags');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'bio' => $this->bio,
        ];
    }

    /**
     * Determine if the profile should be indexed for search.
     */
    public function shouldBeSearchable(): bool
    {
        return !empty($this->username);
    }

    /**
     * Scope a query to only include high engagement profiles.
     */
    public function scopeHighEngagement($query)
    {
        return $query->where('likes_count', '>=', 100000);
    }
}