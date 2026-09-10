# SeedPlanta deployment, backup, and observability

Production domain: **seedplanta.com**  
Application: Laravel 12 modular monolith on PHP 8.2+, MySQL, optional Redis later.

There is no automated deploy pipeline in the repo today. `LAUNCH_CHECKLIST.md` at the project root lists operator tasks. This document is the V1 runbook.

---

## Environments

| Env | Purpose | APP_DEBUG | Data |
| --- | --- | --- | --- |
| local | XAMPP / `php artisan serve` | true | Disposable |
| staging (recommended) | staging.seedplanta.com | false | Anonymized copy of prod schema |
| production | seedplanta.com | **false** | Real customers and orders |

Do not deploy untested main-branch commits directly to production. Flow:

```
Git → tests (php artisan test) → build assets if needed → deploy code
  → php artisan migrate --force
  → php artisan config:cache && route:cache && view:cache
  → restart queue workers
  → GET https://seedplanta.com/up
```

---

## Current runtime assumptions

From `.env.example`:

- `DB_CONNECTION=mysql`, database `seeds_bazar`
- `SESSION_DRIVER=database`
- `QUEUE_CONNECTION=database`
- `CACHE_STORE=database`
- `FILESYSTEM_DISK=local` (uploads actually use the `public` disk in code)
- `MAIL_MAILER=log` — **not acceptable in production**
- Redis variables present but unused

PHP public document root must be `/public`. Do not expose `app/`, `.env`, or `storage/` except the `public/storage` link.

---

## Production server components

1. **HTTPS / TLS** on seedplanta.com (and www → apex or vice versa, one canonical).
2. **DNS** A/AAAA (or ALIAS) to the app host; optional CDN later for `/banner`, `/css`, `/js`, `/storage`.
3. **PHP-FPM + nginx or Apache** (XAMPP is local only).
4. **MySQL 8** managed or local with automated backups.
5. **Queue worker** (database driver is fine for V1):
   ```
   php artisan queue:work database --tries=5 --timeout=120 --sleep=3
   ```
   Supervise with systemd or Supervisor. `queue:restart` after every deploy.
6. **Scheduler** (cron every minute):
   ```
   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
   ```
   Today `routes/console.php` only has `inspire`. V1 will add backup, tracking sync, and `queue:prune-failed` as needed.
7. **`php artisan storage:link`** so `/storage` serves uploaded images until S3+CDN.
8. **Logs** in `storage/logs`. Do not serve that directory publicly.

---

## Required production env

```
APP_NAME=SeedPlanta
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seedplanta.com

LOG_CHANNEL=stack
LOG_LEVEL=error

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp   # or ses/postmark/resend
MAIL_FROM_ADDRESS=orders@seedplanta.com
MAIL_FROM_NAME=SeedPlanta
```

Shiprocket (when M9 lands):

```
SHIPROCKET_EMAIL=
SHIPROCKET_PASSWORD=
SHIPROCKET_TOKEN=          # or generate at runtime
SHIPROCKET_WEBHOOK_SECRET=
```

Object storage (when M13 lands):

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL=
```

Razorpay may remain unset for COD-only launch.

---

## Deploy checklist

1. Maintenance window if migrations lock tables: `php artisan down` (optional).
2. Pull release; `composer install --no-dev --optimize-autoloader`.
3. Front-end: this app ships compiled CSS/JS under `public/` (not Vite-required for current storefront). If Vite is used later, `npm ci && npm run build`.
4. `php artisan migrate --force`
5. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. `php artisan queue:restart`
7. `php artisan up`
8. Hit `/up`, homepage, `/admin/login`, place a staging COD order.
9. Confirm `public/storage` link and write permissions on `storage/` and `bootstrap/cache`.

Never run `migrate:fresh` in production.

---

## Queue and scheduler

| Work | Driver now | V1 |
| --- | --- | --- |
| Order create | Sync, in request | Stay in request (transaction) |
| Shiprocket create shipment | — | `database` queue, retries, timeout |
| Mail | Sync/log | Queue `ShouldQueue` notifications |
| Tracking poll | — | Scheduled command |

Switching `QUEUE_CONNECTION` from `database` to `redis` later requires **no application rewrite** — only env + worker command.

Failed jobs: `failed_jobs` table. Retry: `php artisan queue:retry {id|all}`. Inspect: `php artisan queue:failed`.

If Shiprocket is down: order already committed; shipment row `pending`; job retries; admin can see last_error. **Do not roll back customer orders because the courier API failed.**

---

## Health and observability

### Existing

- `GET /up` — framework health (PHP up). Does not check DB/queue.

### V1 minimum

Add `GET /health` (or extend `/up`) that checks:

- database `SELECT 1`
- cache write/read
- `failed_jobs` count (warn threshold)
- disk free space for `storage/`
- optional: last successful schedule run

Do not expose internals publicly without an IP allow-list or token.

### What to watch

| Signal | Where |
| --- | --- |
| HTTP 5xx | web server + `storage/logs/laravel.log` |
| Queue failures | `failed_jobs` + log |
| Slow queries | MySQL slow query log |
| Shipment failures | `shipments.last_error`, job exceptions |
| App exceptions | `LOG_CHANNEL=stack`; later stderr → host logs |
| Server resources | host CPU/RAM/disk |

Optional later (not V1 blockers): Laravel Pulse, Flare, Sentry. Do not add them until basic logging and `/health` exist.

---

## Backup

### Database

Daily mysqldump (or managed snapshot) retained ≥ 14 days; weekly ≥ 8 weeks.

Example (document actual credentials in the server secret store, not git):

```
mysqldump --single-transaction --routines --triggers -u USER -p seeds_bazar | gzip > /backups/seedplanta-$(date +%F).sql.gz
```

Off-site copy (S3/Backblaze) required. Test restore quarterly.

### Storage

- Local `storage/app/public`: rsync or snapshot with the DB backup.
- S3: enable versioning and lifecycle.

### Application

Git is the application backup. Releases should be tagged (`v1.x.y`). Keep the previous release directory for rollback.

---

## Restore procedures

### Restore database

1. `php artisan down`
2. Stop queue workers.
3. Restore dump into a **new** schema first; sanity-check order counts.
4. Swap schemas / import over production only after check.
5. `php artisan up` and restart workers.

### Restore files

Copy `storage/app/public` (or S3 version) from the backup taken at the same timestamp as the DB dump to avoid missing product images.

### Rollback a deployment

1. Check out previous git tag / previous release directory.
2. `composer install --no-dev --optimize-autoloader`
3. If the new release ran irreversible migrations, **do not** roll back code without a matching down migration. Prefer forward fixes. Destructive downs on orders/inventory are forbidden.
4. Recache config/routes/views; `queue:restart`.
5. Health check.

### Recover failed queue jobs

1. Read exception in `failed_jobs` or `php artisan queue:failed`.
2. Fix cause (credentials, 4xx payload, timeout).
3. `php artisan queue:retry {id}` for Shiprocket jobs — must be idempotent (if AWB already exists, attach it, do not create a duplicate shipment).
4. If the job is poison (invalid order), leave it failed and fix data in admin; do not retry blindly.

---

## Staging

If practical, run a second vhost with `APP_ENV=staging`, `APP_DEBUG=false`, separate DB, `MAIL_MAILER=log` or a catch-all inbox, and Shiprocket **test** credentials. Refresh staging DB from a sanitized dump (scrub emails/phones) before major releases.

---

## Domain cutover notes

- Canonical host: `https://seedplanta.com`
- Force HTTPS at the web server and via `APP_URL`
- Update `robots.txt` Sitemap to absolute `https://seedplanta.com/sitemap.xml` (today it is path-relative `/sitemap.xml`, which is acceptable)
- Search Console + analytics after DNS

---

## What is not automated yet

- CI (GitHub Actions) — add `php artisan test` on PR before launch if the repo is hosted.
- Supervisor config in-repo
- Backup cron in `routes/console.php`
- Real error tracker
- CDN

These are Milestone 21–23 work, not blockers for continuing application development locally.
