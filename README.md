# Uptime Monitor

A Laravel + Vue.js MVP for monitoring client website homepages and emailing clients when a site is down.

## Stack

- Laravel 13
- Vue 3 single page frontend via Vite
- MySQL/MariaDB
- Redis-backed Laravel queues and cache

## Features

- Stores clients with email addresses.
- Stores monitored websites per client.
- Displays a Vue SPA home page with a client email selector.
- Shows the selected client's websites as clickable links.
- Confirms external navigation with a Continue/Cancel dialog.
- Schedules website monitoring every 15 minutes.
- Checks each website with a 10-second timeout.
- Queues one job per website check for scalable processing.
- Sends queued alert emails when a website is unreachable or returns an error.
- Uses `do-not-reply@example.com` as the sender.
- Alert subject and body use the required format: `{website URL} is down!`

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

The app is available at:

```text
http://127.0.0.1:8000
```

## Running The Monitor

Run the scheduler in one terminal:

```bash
php artisan schedule:work
```

Run the queue worker in a second terminal:

```bash
php artisan queue:work
```

The scheduler queues website checks every 15 minutes. The queue worker performs the checks and sends alert emails.

For an immediate manual run:

```bash
php artisan monitor:websites
php artisan queue:work --stop-when-empty
```

## Email Behavior

Local `.env.example` uses the log mailer:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="do-not-reply@example.com"
```

Local emails are written to:

```text
storage/logs/laravel.log
```

For production, configure Laravel's built-in mail driver, such as SES:

```env
MAIL_MAILER=ses
```

## Redis

The project uses Laravel's queue abstraction and includes `predis/predis`, so Redis works without requiring the `phpredis` PHP extension.

Production-style queue/cache settings are in `.env.example`:

```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

Keep `php artisan queue:work` running so queued website checks and alert emails are processed.

## Tests

```bash
php artisan test
```

The test suite covers:

- Client/website API response.
- Queueing website checks from the monitor command.
- Down website status updates.
- Alert email subject, body, recipient, and sender.
- Up website status updates without alerts.
