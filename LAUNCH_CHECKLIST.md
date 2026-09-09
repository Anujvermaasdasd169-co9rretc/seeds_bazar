# Seeds Bazar launch checklist

## Complete in the admin panel

- Add product image, price, stock, description, season, sunlight, germination, harvest, spacing, sowing depth, and difficulty for every active product.
- Replace the policy drafts in **Admin → Store Settings** with business-approved content.
- Set the real WhatsApp number, shipping fee, free-shipping threshold, delivery estimate, and shipping method.
- Change the seeded administrator password and remove unused administrator accounts.

## Configure on the production server

- Set `APP_ENV=production`, `APP_DEBUG=false`, a public HTTPS `APP_URL`, and `SESSION_SECURE_COOKIE=true`.
- Use production MySQL credentials and run `php artisan migrate --force`.
- Configure a real transactional mail provider; do not use the `log` mailer.
- Set a queue worker/supervisor for queued work and scheduled backups.
- Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` after deployment.
- Confirm `public/storage` is linked and writable; back up the database and uploads before every deploy.

## External services still required

- Razorpay live keys plus a verified webhook endpoint.
- A courier/delivery partner if tracking labels and live shipment status are needed.
- Domain, DNS, SSL certificate, analytics, and search-console verification.

## Smoke test before opening orders

- Register a customer, verify email, add an address, and place a COD order.
- Check stock deducts, the admin can update the order, and cancellation restores stock.
- Check every active product page and every policy page on mobile.
- Verify `/sitemap.xml` and `/robots.txt` load from the public domain.
