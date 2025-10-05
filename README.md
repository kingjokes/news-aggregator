# News Aggregator Backend API

A Laravel 12 backend system that aggregates news articles from multiple news sources and provides RESTful API endpoints for frontend consumption.

## Features

-   **Multi-Source Aggregation**: Fetches articles from NewsAPI, The Guardian, and New York Times
-   **RESTful API**: Clean, consistent API endpoints with filtering and pagination
-   **Advanced Search**: Full-text search capabilities across articles
-   **Filtering**: Filter by date range, category, source, and author
-   **Automated Updates**: Scheduled hourly article fetching
-   **SOLID Principles**: Clean architecture with separation of concerns
-   **Test Coverage**: Comprehensive feature tests included

## Technology Stack

-   Laravel 12
-   MySQL/PostgreSQL
-   Guzzle HTTP Client
-   Composer

## Installation

### 1. Clone and Install Dependencies

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configure Database

Update `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_aggregator
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Configure API Keys

Add your API keys to `.env`:

```env
NEWS_API_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYT_API_KEY=your_nyt_key_here
```

**Getting API Keys:**

-   NewsAPI: https://newsapi.org/register
-   The Guardian: https://open-platform.theguardian.com/access/
-   New York Times: https://developer.nytimes.com/get-started

### 4. Run Migrations and Seed

```bash
php artisan migrate
```

### 5. Fetch Initial Articles

```bash
php artisan articles:fetch
```

### 7. Start Development Server

```bash
php artisan serve
```

### 7. Start Laravel Scheduler

```bash
 php artisan schedule:work
```

## API Endpoints

### Articles

#### Get All Articles

```
GET /api/v1/articles
```

**Query Parameters:**

-   `search` - Search in title, description, content
-   `from` - Filter by date (YYYY-MM-DD)
-   `to` - Filter by date (YYYY-MM-DD)
-   `category` - Filter by category ID(s), comma-separated
-   `source` - Filter by source ID(s), comma-separated
-   `author` - Filter by author name(s), comma-separated
-   `per_page` - Items per page (default: 15, max: 100)

**Example:**

```bash
curl "http://localhost:8000/api/v1/articles?search=technology&from=2024-01-01&per_page=20"
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "title": "Article Title",
      "description": "Article description",
      "content": "Full article content",
      "author": "John Doe",
      "url": "https://example.com/article",
      "image_url": "https://example.com/image.jpg",
      "published_at": "2024-01-15T10:30:00Z",
      "source": {
        "id": 1,
        "name": "NewsAPI",
        "slug": "newsapi"
      },
      "category": {
        "id": 2,
        "name": "Technology",
        "slug": "technology"
      }
    }
  ],
  "links": {...},
  "meta": {...}
}
```

#### Get Recent Articles

```
GET /api/v1/articles/recent
```

#### Get Single Article

```
GET /api/v1/articles/{id}
```

#### Get All Authors

```
GET /api/v1/articles/authors
```

**Response:**

```json
{
    "success": true,
    "message": "Authors retrieved successfully",
    "data": ["Aaron Boxerman", "ABC News", "Adam Gabbatt", "Adam Morton"]
}
```

#### Get Articles by Source

```
GET /api/v1/articles/source/${slug}
```

#### Get Articles by Category

```
GET /api/v1/articles/category/${slug}
```

### News Sources

#### Get All Sources

```
GET /api/v1/sources
```

**Response:**

```json
{
    "data": [
        {
            "id": 12,
            "name": "9to5google.com",
            "slug": "9to5googlecom",
            "api_identifier": null,
            "articles_count": 1,
            "created_at": "2025-10-05T14:21:12+00:00",
            "updated_at": "2025-10-05T14:21:12+00:00"
        },

        {
            "id": 11,
            "name": "NBC News",
            "slug": "nbc-news",
            "api_identifier": null,
            "articles_count": 2,
            "created_at": "2025-10-05T14:21:12+00:00",
            "updated_at": "2025-10-05T14:21:12+00:00"
        }
    ]
}
```

#### Get Single Source

```
GET /api/v1/sources/{id}
```

### News Categories

#### Get All Categories

```
GET /api/v1/categories
```

**Response:**

```json
{
    "data": [
        {
            "id": 20,
            "name": "Art and design",
            "slug": "art-and-design",
            "articles_count": 1,
            "created_at": "2025-10-05T14:21:13+00:00",
            "updated_at": "2025-10-05T14:21:13+00:00"
        },

        {
            "id": 7,
            "name": "Opinion",
            "slug": "opinion",
            "articles_count": 4,
            "created_at": "2025-10-05T14:21:13+00:00",
            "updated_at": "2025-10-05T14:21:13+00:00"
        }
    ]
}
```

#### Get Single Category

```
GET /api/v1/categories/{id}
```

## Architecture

### Design Patterns

1. **Adapter Pattern**: Each news source has its own adapter implementing `NewsSourceAdapter` interface
2. **Service Layer**: `ArticleAggregatorService` handles business logic
3. **Repository Pattern**: Eloquent models act as repositories
4. **Dependency Injection**: Services injected via Laravel's container

## SOLID Principles Implementation

1. **Single Responsibility**: Each adapter handles only one news source
2. **Open/Closed**: Easy to add new adapters without modifying existing code
3. **Liskov Substitution**: All adapters implement the same interface
4. **Interface Segregation**: Clean, minimal interface for adapters
5. **Dependency Inversion**: Controllers depend on abstractions, not concrete implementations

## Testing

Run the test suite:

```bash
php artisan test
```

Run specific test:

```bash
php artisan test --filter ArticleApiTest
```

## Scheduled Tasks

Articles are automatically fetched every hour. Manual fetch:

```bash
php artisan articles:fetch
```

## Troubleshooting

### API Rate Limits

If you hit API rate limits, adjust the scheduler frequency in `app/Console/Kernel.php`

## Full-Text Search Not Working

Ensure your database supports full-text indexing (MySQL 5.6+, PostgreSQL with extensions)


## Performance Optimization

-   Database indexes on frequently queried columns
-   Pagination to limit response size
-   Background job processing for heavy operations

## Security

-   API rate limiting enabled (60 requests/minute)
-   Input validation on all endpoints
-   SQL injection protection via Eloquent ORM
-   CORS configuration for frontend integration
