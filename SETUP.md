# Quick Setup Guide

Follow these steps to get your Laravel Queue Forward API up and running:

## Step 1: Start Laragon Services
- Open Laragon
- Click "Start All" button
- Verify MySQL and Redis are running (green indicators)

## Step 2: Create the Database
Open HeidiSQL or your preferred MySQL client and run:
```sql
CREATE DATABASE kuldip_queue;
```

## Step 3: Run Migrations
Open terminal in this directory and run:
```bash
php artisan migrate
```

## Step 4: Generate Your First API Key
```bash
php artisan apikey:generate "My First App"
```
**IMPORTANT:** Copy and save the generated API key!

## Step 5: Start the Queue Worker
Open a new terminal window and run:
```bash
php artisan queue:work redis --tries=3
```
Keep this terminal running!

## Step 6: Test Your API
Use the example in the README.md or test with this curl command:
```bash
curl -X POST http://localhost/api/forward \
  -H "X-API-KEY: your-copied-api-key" \
  -H "Content-Type: application/json" \
  -d "{\"forward_url\": \"https://httpbin.org/post\", \"payload\": {\"test\": \"data\"}}"
```

## Done!
Your API is now ready to forward requests. See README.md for complete documentation.

## Common Issues

**"Connection refused" error during migration:**
- MySQL is not running in Laragon
- Database doesn't exist (run the CREATE DATABASE command)

**Queue jobs not processing:**
- Queue worker is not running (run `php artisan queue:work redis`)
- Redis is not running in Laragon

**401 Unauthorized:**
- API key is incorrect
- API key header is missing (must use `X-API-KEY` header)
