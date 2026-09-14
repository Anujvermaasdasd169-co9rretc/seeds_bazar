# Deploy Seed Planta

Use a VPS or Laravel-friendly host with PHP 8.2+, MySQL, Composer, and a process manager. XAMPP is for local development only.

## Apache

Point the virtual host **document root** at `public/`, not the project root.

```
DocumentRoot /var/www/seeds_bazar/public
<Directory /var/www/seeds_bazar/public>
    AllowOverride All
    Require all granted
</Directory>
```

Enable SSL and set `APP_URL` to the `https://` origin.

## First release

1. Copy `.env.example` to `.env` and set production values: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, a strong `APP_KEY` (`php artisan key:generate`), MySQL credentials that are not empty `root`, `ADMIN_EMAIL` / `ADMIN_PASSWORD`, real WhatsApp, SMTP (or Resend/SES), Razorpay **live** key, secret, and webhook secret.
2. `composer install --no-dev --optimize-autoloader`
3. `php artisan migrate --force`
4. `php artisan storage:link`
5. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. Seed the admin user if needed (`php artisan db:seed --class=AdminSeeder --force`).
7. Health check: `GET /up` must return 200.

## Queue and scheduler

`QUEUE_CONNECTION=database` (or Redis). Keep a worker running:

```
php artisan queue:work --tries=3 --timeout=90
```

Run the scheduler every minute (cron):

```
* * * * * cd /var/www/seeds_bazar && php artisan schedule:run >> /dev/null 2>&1
```

That expires unpaid online orders (`orders:expire-unpaid`).

## Razorpay webhook

Dashboard URL: `https://your-domain.com/webhooks/razorpay`  
Event: `payment.captured`  
Signing secret: `RAZORPAY_WEBHOOK_SECRET`

Shiprocket is optional. Leave those env vars empty to pack and courier orders by hand for the first week.
