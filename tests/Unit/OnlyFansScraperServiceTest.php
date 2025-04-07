<?php

namespace Tests\Unit;

use App\Data\ProfileData;
use App\Models\Profile;
use App\Models\Tag;
use App\Services\OnlyFansScraperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OnlyFansScraperServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the service can scrape a profile successfully.
     */
    public function test_can_scrape_profile(): void
    {
        // Mock the HTTP facade
        Http::fake([
            'app.onlyfansapi.com/api/profile/*' => Http::response([
                'data' => [
                    'username' => 'testuser',
                    'name' => 'Test User',
                    'bio' => 'This is a test bio',
                    'avatar_url' => 'https://example.com/avatar.jpg',
                    'cover_url' => 'https://example.com/cover.jpg',
                    'posts_count' => 100,
                    'photos_count' => 50,
                    'videos_count' => 25,
                    'likes_count' => 1000,
                    'followers_count' => 500,
                    'social_links' => [
                        'twitter' => 'https://twitter.com/testuser',
                        'instagram' => 'https://instagram.com/testuser',
                    ],
                    'last_post_at' => now()->subDay()->toIso8601String(),
                    'tags' => ['fitness', 'model'],
                    'is_verified' => true,
                ],
            ], 200),
        ]);

        // Create the service
        $service = new OnlyFansScraperService();

        // Scrape the profile
        $profileData = $service->scrapeProfile('testuser');

        // Assert that the profile data was returned
        $this->assertNotNull($profileData);
        $this->assertInstanceOf(ProfileData::class, $profileData);
        $this->assertEquals('testuser', $profileData->username);
        $this->assertEquals('Test User', $profileData->name);
        $this->assertEquals('This is a test bio', $profileData->bio);
        $this->assertEquals(100, $profileData->posts_count);
        $this->assertEquals(1000, $profileData->likes_count);
        $this->assertEquals(['fitness', 'model'], $profileData->tags);
        $this->assertTrue($profileData->is_verified);

        // Assert that the HTTP request was made
        Http::assertSent(function ($request) {
            return $request->url() === 'https://app.onlyfansapi.com/api/profile/testuser' &&
                   $request->method() === 'GET' &&
                   $request->hasHeader('Authorization', 'Bearer ' . config('services.onlyfans.api_key'));
        });
    }

    /**
     * Test that the service handles API errors gracefully.
     */
    public function test_handles_api_errors(): void
    {
        // Mock the HTTP facade to return an error
        Http::fake([
            'app.onlyfansapi.com/api/profile/*' => Http::response([
                'error' => 'Profile not found',
            ], 404),
        ]);

        // Create the service
        $service = new OnlyFansScraperService();

        // Scrape the profile
        $profileData = $service->scrapeProfile('nonexistentuser');

        // Assert that null was returned
        $this->assertNull($profileData);

        // Assert that the HTTP request was made
        Http::assertSent(function ($request) {
            return $request->url() === 'https://app.onlyfansapi.com/api/profile/nonexistentuser' &&
                   $request->method() === 'GET';
        });
    }

    /**
     * Test that the service handles rate limiting.
     */
    public function test_handles_rate_limiting(): void
    {
        // Mock the HTTP facade to return a rate limit error
        Http::fake([
            'app.onlyfansapi.com/api/profile/*' => Http::response([
                'error' => 'Rate limit exceeded',
            ], 429, ['Retry-After' => '5']),
        ]);

        // Create the service
        $service = new OnlyFansScraperService();

        // Scrape the profile
        $profileData = $service->scrapeProfile('testuser');

        // Assert that null was returned
        $this->assertNull($profileData);

        // Assert that the HTTP request was made
        Http::assertSent(function ($request) {
            return $request->url() === 'https://app.onlyfansapi.com/api/profile/testuser' &&
                   $request->method() === 'GET';
        });
    }

    /**
     * Test that the service can save a profile.
     */
    public function test_can_save_profile(): void
    {
        // Create a profile data object
        $profileData = new ProfileData(
            username: 'testuser',
            name: 'Test User',
            bio: 'This is a test bio',
            avatar_url: 'https://example.com/avatar.jpg',
            cover_url: 'https://example.com/cover.jpg',
            posts_count: 100,
            photos_count: 50,
            videos_count: 25,
            likes_count: 1000,
            followers_count: 500,
            social_links: [
                'twitter' => 'https://twitter.com/testuser',
                'instagram' => 'https://instagram.com/testuser',
            ],
            last_post_at: now()->subDay(),
            tags: ['fitness', 'model'],
            is_verified: true,
        );

        // Create the service
        $service = new OnlyFansScraperService();

        // Save the profile
        $profile = $service->saveProfile($profileData);

        // Assert that the profile was saved
        $this->assertInstanceOf(Profile::class, $profile);
        $this->assertEquals('testuser', $profile->username);
        $this->assertEquals('Test User', $profile->name);
        $this->assertEquals('This is a test bio', $profile->bio);
        $this->assertEquals(100, $profile->posts_count);
        $this->assertEquals(1000, $profile->likes_count);
        $this->assertTrue($profile->is_verified);

        // Assert that the tags were saved
        $this->assertCount(2, $profile->tags);
        $this->assertTrue($profile->tags->contains('name', 'fitness'));
        $this->assertTrue($profile->tags->contains('name', 'model'));
    }
} 