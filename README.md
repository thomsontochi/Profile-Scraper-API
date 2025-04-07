# OnlyFans Profile Scraper API

A Laravel-based API for scraping and managing OnlyFans profiles. This system provides endpoints for searching profiles, retrieving profile details, and asynchronously scraping new profiles.

## Features

- 🔍 Full-text search for profiles
- 📊 Profile statistics and metrics
- 🏷️ Tag-based categorization
- ⚡ Asynchronous profile scraping
- 🔄 Job status tracking
- 🚀 Rate limiting and error handling
- 📝 Comprehensive logging

## Requirements

- PHP 8.2+
- MySQL 8.0+
- Redis 6.0+
- Composer
- Node.js & NPM (for frontend assets)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd onlyfans-scrapper
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install NPM dependencies:
```bash
npm install
```

4. Copy the environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=onlyfans_scrapper
DB_USERNAME=your_username
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
```

7. Run migrations:
```bash
php artisan migrate
```

8. Start the development server:
```bash
php artisan serve
```

9. Start Laravel Horizon (in a separate terminal):
```bash
php artisan horizon
```

## API Endpoints

### Search Profiles
```
GET /api/profiles/search?query={search_term}
```
Search for profiles using full-text search.

**Parameters:**
- `query` (required): Search term (2-100 characters)

**Response:**
```json
{
    "message": "Profiles retrieved successfully",
    "count": 1,
    "profiles": [...]
}
```

### Get Profile
```
GET /api/profiles/{username}
```
Get detailed information about a specific profile.

**Parameters:**
- `username` (required): Profile username

**Response:**
```json
{
    "message": "Profile retrieved successfully",
    "profile": {
        "id": 1,
        "username": "example",
        "name": "Example User",
        "bio": "...",
        "avatar_url": "...",
        "cover_url": "...",
        "posts_count": 100,
        "photos_count": 80,
        "videos_count": 20,
        "likes_count": 1000,
        "followers_count": 500,
        "social_links": {...},
        "last_post_at": "2025-04-07T13:26:01.000000Z",
        "last_scraped_at": "2025-04-07T13:26:01.000000Z",
        "is_verified": true,
        "tags": [...]
    }
}
```

### Scrape Profile
```
POST /api/profiles/scrape
```
Queue a new profile scraping job.

**Request Body:**
```json
{
    "username": "example"
}
```

**Response:**
```json
{
    "message": "Scrape job queued successfully",
    "job_id": 1
}
```

### Get Scrape Job Status
```
GET /api/profiles/scrape/{jobId}
```
Get the status of a scrape job.

**Parameters:**
- `jobId` (required): Scrape job ID

**Response:**
```json
{
    "message": "Scrape job status retrieved successfully",
    "job": {
        "id": 1,
        "username": "example",
        "status": "completed",
        "error_message": null,
        "created_at": "2025-04-07T13:26:01.000000Z",
        "updated_at": "2025-04-07T13:26:01.000000Z"
    }
}
```

## Error Handling

The API uses standard HTTP status codes and returns detailed error messages:

- `400 Bad Request`: Invalid input parameters
- `404 Not Found`: Resource not found
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server-side error

Error responses include:
```json
{
    "message": "Error description",
    "error": "Detailed error message"
}
```

## Rate Limiting

API endpoints are rate-limited to 60 requests per minute per IP address.

## Logging

The system logs all important events:
- Profile searches
- Profile retrievals
- Scrape job creation and status updates
- Errors and exceptions

Logs can be found in `storage/logs/laravel.log`.

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License.
# Profile-Scraper-API
