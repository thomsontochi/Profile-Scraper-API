<?php

namespace App\Services;

use App\Data\ProfileData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MockOnlyFansService
{
    /**
     * Generate a mock profile response
     */
    public function getMockProfile(string $username): array
    {
        // Debug log
        \Log::info('Generating mock profile data', ['username' => $username]);

        // here i will Generate random but realistic data
        $followersCount = rand(1000, 1000000);
        $likesCount = $followersCount * rand(2, 5);
        $postsCount = rand(50, 500);
        $photosCount = $postsCount * rand(1, 3);
        $videosCount = $postsCount * rand(1, 2);

        return [
            'data' => [
                'username' => $username,
                'name' => $this->generateName(),
                'bio' => $this->generateBio(),
                'avatar_url' => $this->generateAvatarUrl($username),
                'cover_url' => $this->generateCoverUrl($username),
                'posts_count' => $postsCount,
                'photos_count' => $photosCount,
                'videos_count' => $videosCount,
                'likes_count' => $likesCount,
                'followers_count' => $followersCount,
                'social_links' => $this->generateSocialLinks($username),
                'last_post_at' => Carbon::now()->subHours(rand(1, 48))->toIso8601String(),
                'tags' => $this->generateTags(),
                'is_verified' => (bool)rand(0, 1),
            ]
        ];
    }

    /**
     * Generate a realistic name
     */
    private function generateName(): string
    {
        $firstNames = ['Emma', 'Sophia', 'Olivia', 'Ava', 'Isabella', 'Mia', 'Charlotte', 'Amelia', 'Harper', 'Evelyn'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    /**
     * Generate a realistic bio
     */
    private function generateBio(): string
    {
        $bios = [
            "✨ Content Creator | Model | Fitness Enthusiast\n💪 Living my best life\n🌟 DM for collabs",
            "🎭 Actress | Dancer | Creator\n💃 Expressing myself through art\n✨ New content daily",
            "🌺 Lifestyle | Fashion | Travel\n✈️ Exploring the world\n💫 Sharing my journey",
            "🎨 Artist | Photographer | Creator\n📸 Capturing moments\n🌟 Making memories",
            "💋 Model | Influencer | Entrepreneur\n🔥 Building my empire\n✨ Living the dream"
        ];
        
        return $bios[array_rand($bios)];
    }

    /**
     * Generate avatar URL
     */
    private function generateAvatarUrl(string $username): string
    {
        return "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=random";
    }

    /**
     * Generate cover URL
     */
    private function generateCoverUrl(string $username): string
    {
        return "https://source.unsplash.com/random/1200x400/?fashion,model";
    }

    /**
     * Generate social links
     */
    private function generateSocialLinks(string $username): array
    {
        $links = [];
        
        if (rand(0, 1)) {
            $links['instagram'] = "https://instagram.com/" . $username;
        }
        if (rand(0, 1)) {
            $links['twitter'] = "https://twitter.com/" . $username;
        }
        if (rand(0, 1)) {
            $links['tiktok'] = "https://tiktok.com/@" . $username;
        }
        
        return $links;
    }

    /**
     * Generate random tags
     */
    private function generateTags(): array
    {
        $allTags = [
            'fitness', 'model', 'fashion', 'lifestyle', 'travel',
            'photography', 'art', 'dance', 'music', 'food',
            'beauty', 'makeup', 'wellness', 'yoga', 'fitness',
            'gym', 'workout', 'healthy', 'motivation', 'inspiration'
        ];
        
        // Randomly select 3-6 tags
        $numTags = rand(3, 6);
        $selectedTags = array_rand(array_flip($allTags), $numTags);
        
        return is_array($selectedTags) ? $selectedTags : [$selectedTags];
    }
} 