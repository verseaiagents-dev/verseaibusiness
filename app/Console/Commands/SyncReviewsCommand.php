<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserProfile;
use App\Services\ReviewsApiService;
use Illuminate\Support\Facades\Log;

class SyncReviewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:sync {--profile-id= : Sync specific profile} {--all : Sync all profiles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync reviews from configured APIs';

    protected $reviewsApiService;

    public function __construct(ReviewsApiService $reviewsApiService)
    {
        parent::__construct();
        $this->reviewsApiService = $reviewsApiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $profileId = $this->option('profile-id');
        $syncAll = $this->option('all');

        if ($profileId) {
            $profile = UserProfile::find($profileId);
            if (!$profile) {
                $this->error("Profile with ID {$profileId} not found.");
                return 1;
            }
            $this->syncProfile($profile);
        } elseif ($syncAll) {
            $this->syncAllProfiles();
        } else {
            $this->error('Please specify --profile-id or --all option.');
            return 1;
        }

        return 0;
    }

    /**
     * Sync a specific profile
     */
    private function syncProfile(UserProfile $profile)
    {
        $this->info("Syncing reviews for profile: {$profile->business_name}");

        if (!$profile->reviews_api_type || $profile->reviews_api_type === 'manual') {
            $this->warn("Profile {$profile->business_name} has no API configuration or is set to manual.");
            return;
        }

        $result = $this->reviewsApiService->syncReviews($profile);

        if (isset($result['success'])) {
            $this->info("✓ Successfully synced {$result['total_reviews']} reviews for {$profile->business_name}");
            $this->info("  Average rating: {$result['average_rating']}/5");
        } else {
            $this->error("✗ Failed to sync reviews for {$profile->business_name}: {$result['error']}");
            Log::error("Review sync failed for profile {$profile->id}: " . ($result['error'] ?? 'Unknown error'));
        }
    }

    /**
     * Sync all profiles with auto-sync enabled
     */
    private function syncAllProfiles()
    {
        $profiles = UserProfile::where('auto_sync_reviews', true)
            ->whereNotNull('reviews_api_type')
            ->where('reviews_api_type', '!=', 'manual')
            ->get();

        if ($profiles->isEmpty()) {
            $this->info('No profiles found with auto-sync enabled.');
            return;
        }

        $this->info("Found {$profiles->count()} profiles with auto-sync enabled.");

        $successCount = 0;
        $errorCount = 0;

        foreach ($profiles as $profile) {
            try {
                $result = $this->reviewsApiService->syncReviews($profile);

                if (isset($result['success'])) {
                    $this->info("✓ {$profile->business_name}: {$result['total_reviews']} reviews synced");
                    $successCount++;
                } else {
                    $this->error("✗ {$profile->business_name}: {$result['error']}");
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->error("✗ {$profile->business_name}: Exception - {$e->getMessage()}");
                $errorCount++;
                Log::error("Review sync exception for profile {$profile->id}: " . $e->getMessage());
            }
        }

        $this->info("\nSync Summary:");
        $this->info("✓ Successful: {$successCount}");
        $this->info("✗ Failed: {$errorCount}");
    }
} 