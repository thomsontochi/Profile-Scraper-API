<?php

namespace App\Jobs;

use App\Models\Profile;
use App\Models\ScrapeJob;
use App\Services\OnlyFansScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeProfileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $username,
        public ?int $scrapeJobId = null,
    ) {
        $this->onQueue('scraping');
    }

    /**
     * Execute the job.
     */
    public function handle(OnlyFansScraperService $scraperService): void
    {
        try {
            // Update the scrape job status if available
            if ($this->scrapeJobId) {
                ScrapeJob::where('id', $this->scrapeJobId)
                    ->update(['status' => 'processing']);
            }

            // Scrape the profile
            $profileData = $scraperService->scrapeProfile($this->username);

            if (!$profileData) {
                $this->markAsFailed('Failed to scrape profile data');
                return;
            }

            // Save the profile
            $profile = $scraperService->saveProfile($profileData);
            
            // Update the scrape job status if available
            if ($this->scrapeJobId) {
                ScrapeJob::where('id', $this->scrapeJobId)
                    ->update(['status' => 'completed']);
            }

            Log::info('Profile scraped successfully', [
                'username' => $this->username,
                'likes_count' => $profile->likes_count,
            ]);
        } catch (\Exception $e) {
            $this->markAsFailed($e->getMessage());
            
            // Re-throw the exception to trigger job retries
            throw $e;
        }
    }

    /**
     * Mark the job as failed.
     */
    private function markAsFailed(string $errorMessage): void
    {
        Log::error('Profile scrape job failed', [
            'username' => $this->username,
            'error' => $errorMessage,
        ]);

        // Update the scrape job status if available
        if ($this->scrapeJobId) {
            ScrapeJob::where('id', $this->scrapeJobId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                ]);
        }
    }

    /**
     * The job failed to process.
     */
    public function failed(\Exception $exception): void
    {
        $this->markAsFailed($exception->getMessage());
    }
}