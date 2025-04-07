<?php

namespace App\Services;

use App\Data\ProfileData;
use App\Models\Profile;
use App\Models\Tag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OnlyFansScraperService
{
    /**
     * The mock service instance.
     */
    protected MockOnlyFansService $mockService;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->mockService = new MockOnlyFansService();
    }

    /**
     * Scrape a profile by username.
     */
    public function scrapeProfile(string $username): ?ProfileData
    {
        try {
            // Check if we have a cached response
            $cacheKey = "onlyfans_profile_{$username}";
            if (Cache::has($cacheKey)) {
                Log::info('Using cached profile data', ['username' => $username]);
                return Cache::get($cacheKey);
            }

            // Get mock profile data
            $response = $this->mockService->getMockProfile($username);
            
            if (!$response) {
                Log::error('Failed to generate mock profile data', [
                    'username' => $username,
                ]);
                return null;
            }
            
            // Map the response to a ProfileData object
            $profileData = $this->mapResponseToProfileData($response, $username);
            
            // Cache the profile data for 1 hour
            Cache::put($cacheKey, $profileData, now()->addHour());
            
            return $profileData;
        } catch (\Exception $e) {
            Log::error('Exception while scraping profile', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Map API response to ProfileData DTO.
     */
    private function mapResponseToProfileData(array $response, string $username): ProfileData
    {
        // Extract the profile data from the response
        $profile = $response['data'] ?? $response;
        
        return new ProfileData(
            username: $username,
            name: $profile['name'] ?? null,
            bio: $profile['bio'] ?? null,
            avatar_url: $profile['avatar_url'] ?? null,
            cover_url: $profile['cover_url'] ?? null,
            posts_count: $profile['posts_count'] ?? 0,
            photos_count: $profile['photos_count'] ?? 0,
            videos_count: $profile['videos_count'] ?? 0,
            likes_count: $profile['likes_count'] ?? 0,
            followers_count: $profile['followers_count'] ?? 0,
            social_links: $profile['social_links'] ?? null,
            last_post_at: isset($profile['last_post_at']) ? Carbon::parse($profile['last_post_at']) : null,
            tags: $profile['tags'] ?? [],
            is_verified: $profile['is_verified'] ?? false,
        );
    }

    /**
     * Save or update a profile in the database.
     */
    public function saveProfile(ProfileData $profileData): Profile
    {
        // Find or create the profile
        $profile = Profile::updateOrCreate(
            ['username' => $profileData->username],
            [
                'name' => $profileData->name,
                'bio' => $profileData->bio,
                'avatar_url' => $profileData->avatar_url,
                'cover_url' => $profileData->cover_url,
                'posts_count' => $profileData->posts_count,
                'photos_count' => $profileData->photos_count,
                'videos_count' => $profileData->videos_count,
                'likes_count' => $profileData->likes_count,
                'followers_count' => $profileData->followers_count,
                'social_links' => $profileData->social_links,
                'last_post_at' => $profileData->last_post_at,
                'last_scraped_at' => now(),
                'is_verified' => $profileData->is_verified,
            ]
        );

        // Sync tags
        if (!empty($profileData->tags)) {
            $tagIds = [];
            
            foreach ($profileData->tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            
            $profile->tags()->sync($tagIds);
        }

        return $profile;
    }
}