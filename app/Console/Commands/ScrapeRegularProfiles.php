<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeProfileJob;
use App\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ScrapeRegularProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:regular';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape regular profiles (less than 100k likes)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting scraping regular profiles...');

        // Get profiles with less than 100k likes that haven't been scraped in the last 72 hours
        $profiles = Profile::where('likes_count', '<', 100000)
            ->where(function ($query) {
                $query->whereNull('last_scraped_at')
                    ->orWhere('last_scraped_at', '<', Carbon::now()->subHours(72));
            })
            ->get();

        $count = $profiles->count();
        $this->info("Found {$count} regular profiles to scrape");

        foreach ($profiles as $profile) {
            ScrapeProfileJob::dispatch($profile->username);
            $this->line("Queued scraping job for {$profile->username}");
        }

        $this->info('All regular profile scraping jobs have been queued');

        return Command::SUCCESS;
    }
}