<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeProfileJob;
use App\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ScrapeHighEngagementProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:high-engagement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape profiles with over 100k likes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting scraping high engagement profiles...');

        // Get profiles with over 100k likes that haven't been scraped in the last 24 hours
        $profiles = Profile::highEngagement()
            ->where(function ($query) {
                $query->whereNull('last_scraped_at')
                    ->orWhere('last_scraped_at', '<', Carbon::now()->subDay());
            })
            ->get();

        $count = $profiles->count();
        $this->info("Found {$count} high engagement profiles to scrape");

        foreach ($profiles as $profile) {
            ScrapeProfileJob::dispatch($profile->username);
            $this->line("Queued scraping job for {$profile->username}");
        }

        $this->info('All high engagement profile scraping jobs have been queued');

        return Command::SUCCESS;
    }
}