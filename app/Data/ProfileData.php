<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Illuminate\Support\Carbon;

class ProfileData extends Data
{
    public function __construct(
        public string $username,
        public ?string $name = null,
        public ?string $bio = null,
        public ?string $avatar_url = null,
        public ?string $cover_url = null,
        public int $posts_count = 0,
        public int $photos_count = 0,
        public int $videos_count = 0,
        public int $likes_count = 0,
        public int $followers_count = 0,
        public ?array $social_links = null,
        public ?Carbon $last_post_at = null,
        public ?array $tags = null,
        public bool $is_verified = false,
    ) {
    }
}