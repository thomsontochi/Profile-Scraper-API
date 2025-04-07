<?php

namespace Tests\Feature;

use App\Jobs\ScrapeProfileJob;
use App\Models\Profile;
use App\Models\ScrapeJob;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the search endpoint returns profiles.
     */
    public function test_search_endpoint_returns_profiles(): void
    {
        // Create some profiles
        $profile1 = Profile::factory()->create([
            'username' => 'testuser1',
            'name' => 'Test User 1',
            'bio' => 'This is a test bio 1',
        ]);

        $profile2 = Profile::factory()->create([
            'username' => 'testuser2',
            'name' => 'Test User 2',
            'bio' => 'This is a test bio 2',
        ]);

        $profile3 = Profile::factory()->create([
            'username' => 'anotheruser',
            'name' => 'Another User',
            'bio' => 'This is another bio',
        ]);

        // Make the request
        $response = $this->getJson('/api/profiles/search?query=test');

        // Assert the response
        $response->assertStatus(200)
            ->assertJsonCount(2, 'profiles')
            ->assertJsonStructure([
                'message',
                'count',
                'profiles' => [
                    '*' => [
                        'id',
                        'username',
                        'name',
                        'bio',
                    ],
                ],
            ]);
    }

    /**
     * Test that the show endpoint returns a profile.
     */
    public function test_show_endpoint_returns_profile(): void
    {
        // Create a profile with tags
        $profile = Profile::factory()->create([
            'username' => 'testuser',
            'name' => 'Test User',
            'bio' => 'This is a test bio',
        ]);

        $tag1 = Tag::factory()->create(['name' => 'fitness']);
        $tag2 = Tag::factory()->create(['name' => 'model']);

        $profile->tags()->attach([$tag1->id, $tag2->id]);

        // Make the request
        $response = $this->getJson('/api/profiles/testuser');

        // Assert the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'profile' => [
                    'id',
                    'username',
                    'name',
                    'bio',
                    'tags' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'profile' => [
                    'username' => 'testuser',
                    'name' => 'Test User',
                    'bio' => 'This is a test bio',
                ],
            ]);
    }

    /**
     * Test that the show endpoint returns 404 for non-existent profiles.
     */
    public function test_show_endpoint_returns_404_for_non_existent_profiles(): void
    {
        // Make the request
        $response = $this->getJson('/api/profiles/nonexistentuser');

        // Assert the response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Profile not found',
            ]);
    }

    /**
     * Test that the scrape endpoint queues a job.
     */
    public function test_scrape_endpoint_queues_job(): void
    {
        // Fake the queue
        Queue::fake();

        // Make the request
        $response = $this->postJson('/api/profiles/scrape', [
            'username' => 'testuser',
        ]);

        // Assert the response
        $response->assertStatus(202)
            ->assertJsonStructure([
                'message',
                'job_id',
            ]);

        // Assert that a job was dispatched
        Queue::assertPushed(ScrapeProfileJob::class, function ($job) {
            return $job->username === 'testuser';
        });

        // Assert that a scrape job was created
        $this->assertDatabaseHas('scrape_jobs', [
            'username' => 'testuser',
            'status' => 'pending',
        ]);
    }

    /**
     * Test that the scrape endpoint returns 202 for in-progress jobs.
     */
    public function test_scrape_endpoint_returns_202_for_in_progress_jobs(): void
    {
        // Create a scrape job
        $scrapeJob = ScrapeJob::factory()->create([
            'username' => 'testuser',
            'status' => 'processing',
        ]);

        // Make the request
        $response = $this->postJson('/api/profiles/scrape', [
            'username' => 'testuser',
        ]);

        // Assert the response
        $response->assertStatus(202)
            ->assertJson([
                'message' => 'Scrape job already in progress',
                'job_id' => $scrapeJob->id,
            ]);
    }

    /**
     * Test that the scrape status endpoint returns the job status.
     */
    public function test_scrape_status_endpoint_returns_job_status(): void
    {
        // Create a scrape job
        $scrapeJob = ScrapeJob::factory()->create([
            'username' => 'testuser',
            'status' => 'completed',
        ]);

        // Make the request
        $response = $this->getJson("/api/profiles/scrape/{$scrapeJob->id}");

        // Assert the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'job' => [
                    'id',
                    'username',
                    'status',
                    'error_message',
                ],
            ])
            ->assertJson([
                'job' => [
                    'username' => 'testuser',
                    'status' => 'completed',
                ],
            ]);
    }
} 