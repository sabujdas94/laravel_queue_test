# Laravel Queue Forward API

A Laravel application that receives API requests and forwards them to specified URLs using a Redis-backed queue system.

## Features

- ✅ API Key authentication
- ✅ Request queuing with Redis
- ✅ Automatic request forwarding with retry logic
- ✅ Request status tracking
- ✅ MySQL database for persistence
- ✅ Support for custom HTTP headers

## Requirements

- PHP 8.2+
- MySQL
- Redis
- Composer

## Installation

### 1. Start Laragon Services

Make sure both MySQL and Redis are running in Laragon:
- Open Laragon
- Click "Start All"
- Verify MySQL and Redis are running

### 2. Create Database

Create a new MySQL database named `kuldip_queue`:
```sql
CREATE DATABASE kuldip_queue;
```

Or use Laragon's database management tool (HeidiSQL/PhpMyAdmin).

### 3. Install Dependencies

```bash
composer install
```

### 4. Configure Environment

The `.env` file is already configured with:
- MySQL connection (database: `kuldip_queue`)
- Redis connection
- Queue driver set to Redis

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Generate API Key

```bash
php artisan apikey:generate "My Application"
```

Save the generated API key - you'll need it for API requests.

## Usage

### Starting the Queue Worker

To process queued jobs, run:

```bash
php artisan queue:work redis --tries=3
```

Keep this running in a separate terminal window.

### API Endpoints

#### 1. Forward Request

**Endpoint:** `POST /api/forward`

**Headers:**
```
X-API-KEY: your-api-key-here
Content-Type: application/json
```

**Request Body:**
```json
{
    "forward_url": "https://example.com/api/endpoint",
    "header": {
        "Authorization": "Bearer your-token-here",
        "X-Custom-Header": "custom-value"
    },
    "payload": {
        "key1": "value1",
        "key2": "value2"
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Request queued successfully",
    "request_id": 1
}
```

#### 2. Check Request Status

**Endpoint:** `GET /api/status/{requestId}`

**Headers:**
```
X-API-KEY: your-api-key-here
```

**Response:**
```json
{
    "request_id": 1,
    "status": "completed",
    "forward_url": "https://example.com/api/endpoint",
    "response_status": 200,
    "error_message": null,
    "created_at": "2026-03-10 07:09:47",
    "processed_at": "2026-03-10 07:09:50"
}
```

**Status Values:**
- `pending` - Request is queued but not yet processed
- `processing` - Request is currently being processed
- `completed` - Request was successfully forwarded
- `failed` - Request failed after all retry attempts

### Example Usage with cURL

**Forward a request:**
```bash
curl -X POST http://localhost/api/forward \
  -H "X-API-KEY: sk_your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "forward_url": "https://httpbin.org/post",
    "payload": {
      "message": "Hello World",
      "timestamp": "2026-03-10"
    }
  }'
```

**With custom headers:**
```bash
curl -X POST http://localhost/api/forward \
  -H "X-API-KEY: sk_your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "forward_url": "https://api.example.com/webhook",
    "header": {
      "Authorization": "Bearer target-api-token",
      "X-Webhook-Secret": "secret123"
    },
    "payload": {
      "event": "user.created",
      "data": {"id": 123, "name": "John Doe"}
    }
  }'
```

**Check status:**
```bash
curl -X GET http://localhost/api/status/1 \
  -H "X-API-KEY: sk_your-api-key-here"
```

## Configuration

### Queue Settings

The queue is configured in `config/queue.php` with Redis as the default driver.

**Job settings:**
- Retry attempts: 3
- Timeout: 120 seconds

### Database Tables

**api_keys**
- Stores API keys and their metadata
- Tracks last usage timestamp

**request_logs**
- Stores all forwarding requests
- Tracks status, responses, and errors

**jobs**
- Laravel's default jobs table for queue management

## Management Commands

### Generate API Key
```bash
php artisan apikey:generate "Application Name"
```

### List Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Jobs
```bash
php artisan queue:retry all
```

### Clear Failed Jobs
```bash
php artisan queue:flush
```

## Production Deployment

### Using Laravel Horizon (Linux only)

Horizon provides a dashboard for monitoring queues but requires Linux extensions.

For production on Linux servers:
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

Access the dashboard at: `http://your-domain.com/horizon`

### Using Supervisor (Recommended for Production)

Create a supervisor configuration to keep the queue worker running:

```ini
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --tries=3 --timeout=120
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
```

## Troubleshooting

### Queue not processing
- Ensure Redis is running
- Verify the queue worker is running: `php artisan queue:work`
- Check logs in `storage/logs/`

### Database connection refused
- Start MySQL in Laragon
- Verify database credentials in `.env`
- Ensure database `kuldip_queue` exists

### API Key not working
- Verify the API key is correct
- Check that `is_active` is true in the `api_keys` table
- Ensure you're sending the key in the `X-API-KEY` header

## License

This project is open-sourced software licensed under the MIT license.

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
