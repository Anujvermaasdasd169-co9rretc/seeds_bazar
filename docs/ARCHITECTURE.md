# SeedPlanta architecture

**Product:** SeedPlanta (codebase still named `seeds_bazar`)  
**Stack:** Laravel 12 / PHP 8.2 / MySQL / Blade + vanilla JS  
**Production domain:** seedplanta.com  
**V1 payment:** Cash on Delivery only (Razorpay code exists; do not expand for V1)  
**Shipping partner (target):** Shiprocket — **not implemented**  
**Architecture style:** Modular monolith. Do not introduce microservices.

This document is the Milestone 1 audit. It describes the system as it exists today, what is already production-capable, what is incomplete, and the exact development sequence for V1.

---

## Implementation log

### Milestone 1 — 2026-09-10

Audit complete. Documents in this folder describe the as-is system.

### Security quick wins (M15 partial) — 2026-09-10

- Restored CSRF on `POST /contact` and added `@csrf` to contact forms.
- Removed `is_admin` from `User` mass assignment.
- Throttled admin login and contact submissions.
- Capped image uploads at 2 MB; disallowed SVG logos.
- Admin seeder reads `ADMIN_EMAIL` / `ADMIN_PASSWORD` and refuses the default password in production.
- Inventory restore now clears `stock_deducted_at`.

### Milestone 2 — 2026-09-10 (schema + models)

Additive migrations:

- `products.sku`, `slug`, `mrp`, SEO fields, soft deletes (auto-generated on create)
- `product_images`, `carts`, `cart_items`, `inventory_logs`, `order_sequences`
- Guest/structured address columns on `orders`; SKU/MRP/discount snapshots on `order_items`
- `orders.user_id` no longer cascade-deletes; `products.category_id` restrict-on-delete
- `order_status_histories`, `shipments`, `shipment_tracking_events`

`InventoryService` now writes `inventory_logs` for order deduct and cancellation restore. Public URLs, guest checkout, and server cart are **not** switched yet (M3–M7).

---

## A. Current architecture

The application is a single Laravel 12 web app. There is no dedicated REST API, no mobile app, and no separate frontend framework. Storefront and admin share one codebase, one `users` table, and one session guard.

```
Browser (Blade + public/js/cart.js localStorage)
        │
        ▼
Laravel 12 web routes  ──►  Controllers  ──►  Eloquent models / services
        │                         │
        │                         ├── InventoryService
        │                         ├── ShippingService (flat-rate, local)
        │                         ├── OrderCancellationService
        │                         └── RazorpayGateway (optional online pay)
        ▼
MySQL
  users, categories, products, addresses, orders, order_items,
  reviews, contact_messages, settings, sessions, jobs, cache
```

### Runtime layout

| Layer | Current implementation |
| --- | --- |
| HTTP | `routes/web.php` only. Health check at `/up`. No `routes/api.php`. |
| Auth | Laravel session guard (`web`). Customers and admins are both `User` rows. Admins have `is_admin = true`. Middleware alias `admin` → `EnsureUserIsAdmin`. |
| Storefront | Blade layouts + `public/css/seeds-bazar.css` + `public/js/cart.js`. Cart and wishlist live in `localStorage`. |
| Catalog | `ShopController` loads **all** active products into JSON for client-side filter/search. Categories are hierarchical (max depth 3). Public category URL is `/c/{slug}`. Public product URL is `/products/{id}`. |
| Cart | Client-only. Not persisted on the server. Survives login only because it is in the browser, not because it is merged. |
| Checkout | Requires authentication. Address is chosen from saved addresses. Cart JSON is posted; **server recalculates price and shipping**. COD deducts stock in a DB transaction. Online creates a Razorpay order and deducts stock only after payment verification. |
| Orders | Snapshot of product name, unit, unit price, quantity, line total, and a concatenated shipping address string. Order numbers look like `SB-ymd-XXXXXX`. |
| Inventory | `products.stock_quantity`. Atomic `decrement` with `WHERE stock_quantity >= qty`. No inventory history table. |
| Shipping | Flat rate + free-shipping threshold from `settings` / env. Optional pincode allow-list. No courier API. |
| Admin | Blade admin under `/admin/*`, protected by `auth` + `admin`. Products, categories, orders, contacts, reviews, store settings, profile. |
| Queues | `QUEUE_CONNECTION=database` and `jobs` / `failed_jobs` tables exist. **No Job classes.** Scheduler only has Laravel's `inspire` command. |
| Storage | Product/category/logo images on `public` disk (`storage/app/public`). URLs hardcoded as `/storage/...`. S3 disk is configured but unused. |
| Cache | Database cache store by default. Redis config present, unused. Homepage is not cached. |

### Module map (as-is vs target)

Keep a **modular monolith**. Do not split services. Extract domain logic into `app/` namespaces so modules can later scale independently.

| Module | Today | V1 target |
| --- | --- | --- |
| Auth | Working customer + admin login | Same, plus checkout return URL + guest checkout identity |
| Catalog | Working, ID product URLs, no pagination | Slug URLs, SKU/MRP/SEO, server pagination/search |
| Cart | `localStorage` | Server cart for guests + users, merge on login |
| Checkout | Auth-only, COD + Razorpay | Guest + auth, **COD only** in UI, server validation |
| Orders | Transactional create + snapshots | Unique `SP######` numbers, status history, guest lookup |
| Inventory | Stock column + deduct/restore | Auditable `inventory_logs`, prevent oversell (already partly done) |
| Shipping | Flat rate | Shiprocket via queued jobs; pending shipment if API down |
| Notifications | Email verification / password reset only | Order emails via events; channel-ready |
| Admin | CRUD + dashboard | Inventory, shipments, low stock, status transitions |

### Request flow today

1. Guest browses `/` or `/c/{slug}` or `/products/{id}` — no login required.
2. Add-to-cart writes `{id, name, price, unit, image, quantity}` into `localStorage` key `seeds_bazar_cart`.
3. Cart drawer has **Checkout** (`/checkout`, auth required) and leftover **Purchase on WhatsApp**.
4. Authenticated checkout posts cart IDs + quantities. Server locks products, quotes shipping, creates order + items, deducts stock for COD.
5. Customer sees `/orders/{id}`. Admin updates status from a free-form list (no transition matrix).

---

## B. What is already production-ready

These pieces should be **reused**, not rebuilt.

- Laravel 12 foundation, MySQL, database sessions, database queue tables, `/up` health route.
- Customer auth: register, login, logout, forgot/reset password, email verification, profile, change password.
- Strong password rules, hashed passwords, session regenerate on login, logout invalidates session.
- Rate limits on login, password reset, password change, verification, reviews.
- Address book with Indian pincode validation, ownership checks, default address.
- Category tree (3 levels), unique category slugs, header/home placement, active/hidden storefront rules.
- Product admin CRUD, image upload (MIME-validated), active flag, stock field, cultivation details.
- Checkout **does not trust frontend prices**. Tests prove a manipulated `price: 1` still charges server price.
- COD order creation in a DB transaction with `lockForUpdate` + conditional stock decrement (oversell race covered by tests).
- Order item snapshots (`product_name`, `unit`, `unit_price`, `quantity`, `line_total`). `product_id` is nullable with `nullOnDelete`.
- Order cancellation restores stock once; shipped orders cannot be cancelled; cancelled orders cannot be reactivated.
- Admin gate: customers get 403 on admin routes; non-admin login to `/admin/login` is rejected.
- Policies: shipping, returns, privacy, terms (admin-overridable). Contact form. `robots.txt` + `/sitemap.xml`.
- Product JSON-LD, page titles, meta description, canonical URL, breadcrumbs.
- Feature tests: auth, commerce (COD, stock, payment verify, cancel), storefront, categories, reviews.

---

## C. What is incomplete for V1

| Area | Gap |
| --- | --- |
| Guest checkout | `/checkout` is behind `auth`. No login / register / continue-as-guest step. |
| Cart | No `carts` / `cart_items`. No merge rule. Cart is not revalidated until checkout POST. |
| Product URLs | Public URL is `/products/{id}`. No product slug, SKU, MRP, discount, SEO title/meta columns. |
| Catalog performance | Homepage loads **entire** catalogue + all approved reviews into HTML/JSON. Search/filter is client-side. No pagination. |
| MRP / discount | Product detail JS invents MRP as `price * 1.4`. Not a real catalogue field. |
| Order numbers | `SB-{ymd}-{random}` rather than sequential `SP100001`. |
| Order history accuracy | Address is a single string (OK). No city/state/pincode columns. No discount snapshot per line. User delete **cascades** and would wipe orders. Products are hard-deleted. |
| Inventory audit | No `inventory_logs`. `restoreForOrder` does not clear `stock_deducted_at` (safe today because cancel short-circuits; latent bug). |
| Shiprocket | Not present. No shipment records, tracking, webhooks, or jobs. |
| Queues | Tables exist; nothing is queued. Order emails do not exist. |
| Admin | No inventory screen, no low-stock list, no shipments, no customers list, no sales totals (dashboard counts products/orders/users only). Status updates are unrestricted except cancel. Missing: Out for Delivery, RTO, Returned. |
| Images | Single image, local disk, no resize/compress/WebP/thumbnails. Paths assume `/storage`. |
| Guest order tracking | No order lookup by number + phone/email. |
| Notifications | No order placed/confirmed/shipped/delivered/cancelled mail. |
| Legal | Missing About Us, FAQ, dedicated Cancellation policy. Policies are draft-quality until admin replaces them. |
| Security leftovers | Contact POST is CSRF-exempt. `User.is_admin` is mass-assignable. Admin login is not throttled. Seeded admin password is weak (`seeds123`). Image uploads have no `max:` size. |
| Observability / backup | Logs are default Laravel stack. No scheduled DB backup, no restore runbook, no queue/shipment failure dashboards. |
| WhatsApp order path | Cart still offers WhatsApp purchase — bypasses stock, price, and order engine. Must not be a production checkout path. |

---

## D. What should be refactored (not rewritten)

1. **Keep the storefront UI.** Evolve `cart.js` to talk to server cart endpoints; do not replace Blade with a SPA.
2. **Extract an `OrderService`** from `OrderController::store` so checkout, guest checkout, and tests share one transactional path.
3. **Server-side catalog query** in `ShopController` (paginate, search, sort). Stop dumping the full catalogue into `#shop-products` on the homepage. Product pages may keep a small JSON payload for the current product + related.
4. **Product route model binding by slug**, with 301 from `/products/{id}` if old URLs exist.
5. **Category public URL** keep `/c/{slug}` working and add `/category/{slug}` as the canonical V1 URL (redirect one to the other).
6. **Harden `InventoryService`**: write `inventory_logs`, lock rows, clear `stock_deducted_at` on restore.
7. **Status machine** in one place (`OrderStatus` / `OrderStatusService`) used by customer cancel and admin update.
8. **Image URLs** via a Storage helper / `Storage::url()` so S3 can be enabled with env only.
9. **Remove or demote WhatsApp purchase** to “Need help?” — checkout is the only order path.
10. **Leave Razorpay code in place** behind config, hidden in V1 checkout UI. Do not delete; do not build more payment features.
11. **Delete or stop using** the hardcoded product list in `config/seeds_bazar.php` as a runtime catalogue (seeders may still copy from it once).
12. **Do not add** Elasticsearch, coupons, multi-vendor, or a separate admin SPA.

---

## E. Database changes required

See [DATABASE.md](DATABASE.md) for the full current schema, proposed additive schema, and what to defer.

Summary of required V1 changes (additive migrations only):

- `products`: `slug`, `sku`, `mrp`, `seo_title`, `meta_description`, `deleted_at`; indexes on slug/sku/active.
- `product_images` (gallery; existing `products.image` remains primary until migrated).
- `carts` + `cart_items` (guest token + user_id).
- `inventory_logs`.
- `orders`: structured address snapshot columns, `guest_email` / `guest_phone`, sequential order number support; **stop cascade-deleting orders when a user is deleted**.
- `order_status_histories`.
- `shipments` + `shipment_tracking_events`.
- `notifications` (Laravel database notifications) if in-app history is needed; mail can ship without it.
- Soft deletes on products (and optionally categories).

Defer unless a real product need appears:

- `product_variants` — current `unit` field covers pack size for V1. Add only when a product truly has multiple buyable SKUs.
- Separate `admins` / `roles` tables — `users.is_admin` is enough for a single-operator V1. Add roles when a second staff permission is required.
- Separate `inventory` table — 1:1 with product stock is duplication. Keep stock on `products` (or later on variants) plus `inventory_logs`.

---

## F. Exact development sequence

Implement in this order so each step is testable and does not strand orders.

| Step | Milestone | Why this order |
| --- | --- | --- |
| 1 | M1 Audit (this folder) | Baseline. No feature work without it. |
| 2 | M15 security quick wins | CSRF on contact, un-fillable `is_admin`, admin login throttle, image `max` size, seeder password from env. Cheap and blocks launch bugs. |
| 3 | M2 production database | Additive migrations + model relations. Foundation for cart, inventory audit, shipments. |
| 4 | M8 inventory logs | Wrap existing deduct/restore so every later order path is auditable. |
| 5 | M4 catalog (slug, SKU, MRP, pagination, search) | Public URLs and listing performance before traffic. |
| 6 | M5 server cart | Guest + auth cart, quantity rules, merge on login. |
| 7 | M3 checkout auth UX | Intended URL back to checkout; guest identity; cart merge on register/login. |
| 8 | M6 + M7 COD checkout / order engine | Guest checkout, COD-only UI, `SP` order numbers, snapshots, transaction. |
| 9 | M12 order status machine + history | Before admin shipment work. |
| 10 | M10 queue skeleton | Database queue worker + failed job handling, before Shiprocket. |
| 11 | M9 Shiprocket | Create pending shipment, job, retries, tracking; never block order commit. |
| 12 | M11 admin gaps | Customers, inventory, low stock, shipments, basic sales. |
| 13 | M13 image/storage abstraction | Local now, S3-ready; thumbnails on upload. |
| 14 | M14 performance | Indexes, eager load, listing cache-ready, homepage not full catalogue. |
| 15 | M16 SEO | `/product/{slug}`, `/category/{slug}`, sitemap, structured data, inactive handling. |
| 16 | M17 + M18 CX + notifications | Order success, tracking, emails on domain events. |
| 17 | M19 legal pages | About, FAQ, cancellation; COD and support visible. |
| 18 | M20 tests | Expand coverage after each of the above; full matrix before launch. |
| 19 | M21–M23 deploy, backup, observability | Staging, SSL, workers, backups, health, log/error tracking. |

Razorpay stays compiled and tested but is **not a V1 launch path**.

---

## G. Estimated complexity

Scale: **S** = 0.5–1 day, **M** = 2–4 days, **L** = 5–8 days, **XL** = 8–15 days. Assumes one senior engineer, existing UI reused, no redesign.

| Milestone | Complexity | Notes |
| --- | --- | --- |
| 1 Architecture audit | S | Complete. |
| 2 Production database | M | Additive migrations, backfill slugs/SKUs, FK fixes. |
| 3 Customer auth (guest + return-to-checkout) | M | Auth itself is done; guest + intended URL + merge is the work. |
| 4 Product & catalog | M–L | Slugs, listing query, pagination, admin fields, redirects. |
| 5 Cart | M | New persistence + merge rule + JS wiring. |
| 6 Checkout COD | M | Guest details form, server validation, hide online pay. |
| 7 Order engine | M | Mostly extract/harden existing transaction. |
| 8 Inventory engine | S–M | Logs + restore flag fix on top of working deduct. |
| 9 Shiprocket | L | New integration, jobs, webhooks, failure modes. |
| 10 Queues | S | Config + worker + first jobs. |
| 11 Admin panel | M | New screens on existing admin layout. |
| 12 Order status system | S–M | Transition map + history table. |
| 13 Images & storage | M | Intervention/GD, disks, thumbs. |
| 14 Performance | S–M | Query/index/cache; not a rewrite. |
| 15 Security hardening | S–M | Several small, high-value fixes. |
| 16 SEO | S–M | URLs + sitemap + schema already partly there. |
| 17 Customer experience | M | Success page, guest tracking, address already exists. |
| 18 Notifications | M | Event/listener mail; WhatsApp later. |
| 19 Legal & trust | S | Pages + footer; content from business. |
| 20 QA / tests | L | Large matrix; grow with each milestone. |
| 21 Deployment | M | Server/DNS/SSL/workers — ops more than code. |
| 22 Backup & recovery | S–M | Scripts + documentation. |
| 23 Observability | S–M | Health, log routing, failed jobs, slow query log. |

**Critical path to first paid COD order (lean launch):** M2 → M8 → M5 → M3 → M6/M7 → M15/M20.  
**Critical path to courier-backed launch:** add M10 → M9 → M12 → M17/M18 → M21/M22.

---

## Coding conventions for V1

- Prefer new service classes over fat controllers.
- All money as `decimal(12,2)` in MySQL; never trust client amounts.
- All stock changes go through `InventoryService`.
- All order status changes go through one status service.
- External HTTP (Shiprocket, mail) after `COMMIT`, via queue.
- Secrets only in `.env`.
- Feature tests for every checkout, inventory, and authorization change.
- Do not break existing tests when adding guest checkout — extend them.

## Related documents

- [DATABASE.md](DATABASE.md) — schema audit and target model
- [API.md](API.md) — HTTP surface (web + JSON)
- [SECURITY.md](SECURITY.md) — threats and hardening
- [DEPLOYMENT.md](DEPLOYMENT.md) — environments, workers, backup, rollback
