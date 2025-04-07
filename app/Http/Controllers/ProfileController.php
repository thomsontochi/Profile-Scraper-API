<?php

namespace App\Http\Controllers;

use App\Jobs\ScrapeProfileJob;
use App\Models\Profile;
use App\Models\ScrapeJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Search for profiles.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2|max:100',
            ], [
                'query.required' => 'A search query is required',
                'query.min' => 'The search query must be at least 2 characters',
                'query.max' => 'The search query cannot exceed 100 characters',
            ]);

            $query = $request->input('query');
            $profiles = Profile::search($query)->get();

            Log::info('Profile search completed', [
                'query' => $query,
                'count' => $profiles->count(),
            ]);

            return response()->json([
                'message' => 'Profiles retrieved successfully',
                'count' => $profiles->count(),
                'profiles' => $profiles,
            ]);
        } catch (\Exception $e) {
            Log::error('Profile search failed', [
                'error' => $e->getMessage(),
                'query' => $request->input('query'),
            ]);

            return response()->json([
                'message' => 'Failed to search profiles',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a profile by username.
     */
    public function show(string $username): JsonResponse
    {
        try {
            if (empty($username)) {
                return response()->json([
                    'message' => 'Username is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $profile = Profile::where('username', $username)->first();

            if (!$profile) {
                Log::info('Profile not found', ['username' => $username]);
                return response()->json([
                    'message' => 'Profile not found',
                ], Response::HTTP_NOT_FOUND);
            }

            // Load the tags relationship
            $profile->load('tags');

            Log::info('Profile retrieved successfully', ['username' => $username]);

            return response()->json([
                'message' => 'Profile retrieved successfully',
                'profile' => $profile,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve profile', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Scrape a profile by username.
     */
    public function scrape(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'username' => 'required|string|max:100',
            ], [
                'username.required' => 'A username is required',
                'username.max' => 'The username cannot exceed 100 characters',
            ]);

            $username = $request->input('username');

            // Check if a scrape job for this username is already in progress
            $existingJob = ScrapeJob::where('username', $username)
                ->whereIn('status', ['pending', 'processing'])
                ->first();

            if ($existingJob) {
                Log::info('Scrape job already in progress', [
                    'username' => $username,
                    'job_id' => $existingJob->id,
                ]);

                return response()->json([
                    'message' => 'Scrape job already in progress',
                    'job_id' => $existingJob->id,
                ], Response::HTTP_ACCEPTED);
            }

            // Create a new scrape job
            $scrapeJob = ScrapeJob::create([
                'username' => $username,
                'status' => 'pending',
            ]);

            // Dispatch the job
            ScrapeProfileJob::dispatch($username, $scrapeJob->id);

            Log::info('Scrape job queued successfully', [
                'username' => $username,
                'job_id' => $scrapeJob->id,
            ]);

            return response()->json([
                'message' => 'Scrape job queued successfully',
                'job_id' => $scrapeJob->id,
            ], Response::HTTP_ACCEPTED);
        } catch (\Exception $e) {
            Log::error('Failed to queue scrape job', [
                'username' => $request->input('username'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to queue scrape job',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the status of a scrape job.
     */
    public function scrapeStatus(int $jobId): JsonResponse
    {
        try {
            $job = ScrapeJob::findOrFail($jobId);

            Log::info('Scrape job status retrieved', [
                'job_id' => $jobId,
                'status' => $job->status,
            ]);

            return response()->json([
                'message' => 'Scrape job status retrieved successfully',
                'job' => $job,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve scrape job status', [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve scrape job status',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}