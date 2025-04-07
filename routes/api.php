<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Profile search and retrieval
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/profiles/search', [ProfileController::class, 'search']);
    Route::get('/profiles/{username}', [ProfileController::class, 'show']);
    Route::post('/profiles/scrape', [ProfileController::class, 'scrape']);
    Route::get('/profiles/scrape/{jobId}', [ProfileController::class, 'scrapeStatus']);
});