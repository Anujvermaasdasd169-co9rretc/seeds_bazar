# SeedPlanta security

V1 launch bar: HTTPS, no secret leakage, no price/stock trust from the browser, no customer access to admin, recoverable orders.

This audit is based on the current Laravel 12 codebase (September 2026).

---

## What is already in good shape

- Passwords hashed (`hashed` cast / `Hash::make`). Never stored plain.
- Login regenerates session; logout invalidates session and CSRF token.
- Password reset uses Laravel broker; responses do not reveal whether an email exists.
- Login error is generic (`The provided credentials are incorrect.`).
- Strong registration passwords: min 8, mixed case, numbers, symbols.
- Email verification via signed URLs.
- CSRF on web forms (Laravel default) except the contact exception below.
- XSS: Blade `{{ }}` escaping; cart JS uses `escapeHtml` / `escapeAttr` for drawer rendering. Policy custom HTML uses `e()` then `nl2br`.
- Eloquent / query builder — no raw concatenated SQL in app code reviewed.
- Checkout ignores client prices; stock checked with `lockForUpdate` + conditional `decrement`.
- Order/address authorization uses owner checks and 404 (no IDOR on other customers' orders in tests).
- Admin middleware `EnsureUserIsAdmin` returns 403 for customers.
- Admin login rejects non-admin users and invalidates that session.
- `robots.txt` disallows `/admin/`, `/account/`, `/checkout`, `/orders/`.
- `.env` is gitignored. Razorpay keys read from env via `config/services.php`.
- `APP_DEBUG` must be false in production (documented in launch checklist; not enforced in code).
- `URL::forceScheme('https')` when `APP_URL` is https.
- `trustProxies(at: '*')` — correct behind a TLS terminator; ensure the proxy is trusted at the host level.
- Health endpoint `/up` does not expose debug data.

---

## Findings (fix before production)

### High

1. **Contact POST is CSRF-exempt** (`bootstrap/app.php` `validateCsrfTokens(except: ['contact'])`).  
   The modal posts with `FormData` and can include the Blade CSRF token instead. Exemption allows cross-site spam/CSRF. **Remove the exception.**

2. **`User::$fillable` includes `is_admin`.**  
   Any future `$user->update($request->all())` or mass assignment from request becomes a privilege-escalation bug. Remove `is_admin` from fillable; set it only in seeders/admin code via `forceFill`.

3. **Seeded administrator** (`AdminSeeder`): email `seeds@gmail.com`, password `seeds123`.  
   Production must not use this. Password must come from env (`ADMIN_EMAIL`, `ADMIN_PASSWORD`) and the seeder must refuse to run weak defaults when `APP_ENV=production`.

4. **WhatsApp “Purchase” path** bypasses checkout, inventory, and order records.  
   Treat as a support link only. Production orders must go through the order engine.

5. **Orders cascade-delete with users.**  
   An account-deletion feature (or manual DB delete) would destroy financial history. Change FK to restrict or nullify; soft-delete users.

### Medium

6. **Admin login is not rate-limited.** Customer login is. Add the same throttle (and lockout logging).

7. **Image uploads** validate MIME (`jpeg,png,jpg,webp,gif`) but **no max size**, no dimension cap, no re-encoding. Risk: storage exhaustion and stored XSS via crafted SVG (SVG is not in the MIME list — keep it that way). Add `max:2048` (or similar) and re-encode on store.

8. **Product destroy is a hard delete.** Order items null `product_id` but admin loses the catalogue row. Soft delete.

9. **Category cascade deletes products.** Dangerous if a category is removed in admin. Restrict delete while products exist (admin already may check — verify in CategoryController) or soft-delete.

10. **Checkout not throttled.** Duplicate submit is partly handled by stock locks, but add idempotency (disable button + server throttle) to reduce double COD orders from double-click (two orders of remaining stock can still succeed if stock ≥ 2× qty).

11. **Session cookies:** `SESSION_SECURE_COOKIE` defaults false; `SESSION_ENCRYPT` false. Production: `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY` Laravel default true, `SameSite=lax`. Set `APP_URL=https://seedplanta.com`.

12. **Contact form** stores raw user content and IP. Add throttle. Do not render contact `query` as HTML in admin.

13. **Reviews** can be posted by guests for any active product ID (spam). Throttle exists (5/min). Keep moderation (already pending→approved).

### Low

14. **Razorpay key id** is sent to the payment Blade view (expected for Checkout.js). Key **secret** stays server-side. Fine. V1 COD will not expose this page in the main flow.

15. **Hardcoded catalogue** in `config/seeds_bazar.php` is not a secret but is stale duplication.

16. **Mass assignment on Product** is used with validated arrays in admin — acceptable.

17. **JSON product dump** on homepage exposes stock quantities and descriptions to the client — expected for a storefront; do not dump admin-only fields.

18. **Error handling** in `bootstrap/app.php` `withExceptions` is empty — Laravel defaults hide traces when `APP_DEBUG=false`. Keep it false.

---

## Threat model (V1)

| Threat | Mitigation |
| --- | --- |
| Price manipulation | Server pricing only (already) |
| Qty 0 / negative | Validation `min:1`; cart service reject |
| Inactive / deleted product purchase | `is_active` + soft delete + checkout revalidation |
| Oversell | Transaction + `where stock >= qty` decrement (already); keep for guest checkout |
| CSRF | Restore contact CSRF; keep Blade tokens |
| XSS | Escape output; no SVG uploads; CSP later |
| SQLi | Eloquent only |
| Admin takeover | Unfillable is_admin; strong admin password; throttle; 403 middleware |
| Session theft | HTTPS + Secure + HttpOnly cookies |
| Credential stuffing | Login throttle; consider CAPTCHA only if abused |
| File upload abuse | MIME + size + re-encode + random stored names (`store()` already hashes names) |
| Information leak | `APP_DEBUG=false`; 404 on others' orders; generic password reset |
| Shiprocket credential leak | Server jobs only; env; never in JS |
| Webhook forgery | Shared secret header; HTTPS only |

---

## Production configuration (required)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seedplanta.com
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.seedplanta.com   # only if needed for www
LOG_LEVEL=error
MAIL_MAILER=<real provider>
```

Never commit `.env`, `.env.production`, or API keys. Rotate anything that was used in local dumps.

---

## Authorization matrix

| Actor | Storefront | Checkout | Own orders | Admin |
| --- | --- | --- | --- | --- |
| Guest | Yes | V1 yes (not today) | Track via order number + phone | No |
| Customer | Yes | Yes | Own only | 403 |
| Admin | Yes | Yes | Yes | Yes |

V1 does not need RBAC. One admin role. Do not put `is_admin` in Form Requests.

---

## File upload policy

- Disk: `public` locally; `s3` when `FILESYSTEM_DISK` / a dedicated `media` disk is set.
- Allow: jpeg, png, webp (gif optional). Deny SVG, HTML, PHP.
- Max size: 2 MB (adjust in validation).
- Store hashed names under `products/`, `categories/`, `logo/`.
- Re-encode to JPEG/WebP; strip metadata.
- Serve via `Storage::url()` not concatenated `/storage/`.

---

## Secrets inventory

| Secret | Location | Notes |
| --- | --- | --- |
| APP_KEY | .env | Required |
| DB_* | .env | |
| RAZORPAY_KEY_SECRET | .env | Unused on COD path but keep out of git |
| Future SHIPROCKET_* | .env | Not present yet |
| MAIL_* / AWS_* | .env | |
| Admin password | must be env, not seeder literal | Defect today |

`config/seeds_bazar.php` WhatsApp default `919876543210` is a placeholder — replace via settings/env.

---

## Security test checklist (M20)

- Guest cannot PATCH `/admin/orders/{id}`.
- Customer cannot `forceFill` is_admin via profile update.
- Checkout with forged price still uses server price.
- Checkout with qty 0 / negative rejected.
- Checkout with inactive product rejected.
- CSRF token missing on POST `/contact` → 419 after fix.
- XSS payload in product name / review / contact does not execute in admin or storefront.
- Login and admin login return 429 after limit.
- `/orders/{other}` → 404.
- Shiprocket env values absent from HTML/JS source.
