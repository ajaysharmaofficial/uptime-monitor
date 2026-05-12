# Uptime Monitor

A Laravel and Vue.js MVP for monitoring client website homepages and notifying clients when a site is down.

This project was built for a full-stack developer assessment. The focus is the core monitoring workflow: manually managed clients, scheduled uptime checks, queued processing, and email alerts.

## Overview

Clients are stored with an email address and a list of websites to monitor. Every 15 minutes, the scheduler queues website checks. Queue workers process each website independently, mark the latest status, and queue an alert email when a site is unreachable within 10 seconds or returns an error response.

```text
Client + Websites in MySQL
        |
        v
Laravel Scheduler, every 15 minutes
        |
        v
monitor:websites command
        |
        v
CheckWebsite queued jobs
        |
        v
HTTP check with 10 second timeout
        |
        v
Status update + alert email when down
```

## Tech Stack

- Laravel 13
- Vue 3 SPA with Vite
- Tailwind CSS
- MySQL or MariaDB
- Laravel queues, Redis-ready
- Laravel mail drivers, SES/SMTP-ready
- PHPUnit feature tests

## Features

- Client records with unique email addresses.
- Up to 10 monitored websites per client by deployment convention.
- Vue home page with a client email select input.
- Selected client's websites shown as a bullet list of clickable links.
- Continue/Cancel confirmation dialog before opening a website.
- Website checks scheduled every 15 minutes.
- 10-second HTTP timeout for each website.
- Non-successful responses and connection failures are treated as down.
- Queued one-job-per-website monitoring for scalable processing.
- Queued down-alert emails.
- Required email format:

```text
Subject: {website URL} is down!
Body:    {website URL} is down!
From:    do-not-reply@example.com
```

## Requirement Mapping

| Assessment requirement | Implementation |
| --- | --- |
| Client provides email address | `clients.email` |
| Client submits websites | `websites.url` with `client_id` |
| Manual DB entry during deployment | Migrations and seeders are included |
| Check homepages every 15 minutes | Laravel scheduler in `routes/console.php` |
| Timeout within 10 seconds | `Http::timeout(10)` in `WebsiteMonitor` |
| Alert on unreachable/error | Down status + queued `WebsiteDown` mailable |
| Email subject/body format | `App\Mail\WebsiteDown` |
| Sender address | `do-not-reply@example.com` |
| Client email select input | Vue component `UptimeMonitor.vue` |
| Website hyperlink list | Vue component `UptimeMonitor.vue` |
| Continue/Cancel dialog | Vue component `UptimeMonitor.vue` |
| MySQL/MariaDB | `.env.example` database configuration |
| Redis installed in production | Redis queue/cache config and `predis/predis` |
| No authentication | Public internal SPA route only |

## Local Setup

Install dependencies:

```bash
composer install
npm install
```

Create the environment file:

```bash
cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uptime_monitor
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and sample seed data:

```bash
php artisan migrate --seed
```

Build frontend assets and start the app:

```bash
npm run build
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

## Running Monitoring

Run the scheduler in one terminal:

```bash
php artisan schedule:work
```

Run the queue worker in a second terminal:

```bash
php artisan queue:work
```

The scheduler queues website checks every 15 minutes. The queue worker performs the checks and processes alert emails.

For an immediate manual check:

```bash
php artisan monitor:websites
php artisan queue:work --stop-when-empty
```

## Mail Configuration

Local development can use the log mailer:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="do-not-reply@example.com"
```

Logged emails are written to:

```text
storage/logs/laravel.log
```

For real delivery, configure a Laravel-supported mail driver such as SMTP or SES:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-account@example.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS="do-not-reply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

For production, SES or a verified domain sender is preferred so `do-not-reply@example.com` can be delivered reliably.

## Redis

The monitoring flow uses Laravel queues. Locally, the database queue works well for simple setup. In production, switch to Redis by updating `.env`:

```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

`predis/predis` is included, so Redis works without requiring the `phpredis` PHP extension.

## Useful Commands

```bash
php artisan schedule:list
php artisan monitor:websites
php artisan queue:work
php artisan queue:restart
php artisan config:clear
```

## Tests

Run the test suite:

```bash
php artisan test
```

The tests cover:

- Client and website API response.
- Monitor command queueing one job per website.
- Down website status updates.
- Alert email subject, body, sender, and recipient.
- Up website status updates without alerts.

## Project Structure

```text
app/
  Http/Controllers/ClientWebsiteController.php
  Jobs/CheckWebsite.php
  Mail/WebsiteDown.php
  Models/Client.php
  Models/Website.php
  Services/WebsiteMonitor.php

resources/
  js/components/UptimeMonitor.vue
  views/app.blade.php
  views/emails/website-down.blade.php

database/
  migrations/
  seeders/DatabaseSeeder.php

routes/
  web.php
  console.php
```

## Notes For Reviewers

- Website checks are queued independently so multiple workers can process checks in parallel.
- `withoutOverlapping()` prevents scheduler overlap if a previous run is still active.
- The frontend intentionally has no authentication because the assessment states the app is not publicly accessible.
- Client and website records are expected to be inserted manually during deployment; seed data is included only for local demonstration.
