# SeedPlanta HTTP surface

V1 is a **server-rendered Laravel web app**. There is no public versioned REST API and no `routes/api.php`. JSON is used only where the current storefront already needs it (checkout quote, contact modal).

Do not build a separate Storefront API for V1. Prefer web routes + JSON responses on the same session/CSRF stack. If a mobile app appears later, introduce `/api/v1` with Sanctum without moving web checkout.

---

## Conventions

- CSRF required on all state-changing web routes (see SECURITY.md — contact is currently exempt; that is a defect).
- Validation via Form Requests where they exist.
- Money and stock are never accepted from the client as source of truth. Cart POST may send `id` + `quantity` only.
- Admin routes live under `/admin` and require `auth` + `admin`.
- Health: `GET /up` (Laravel default).

---

## Public storefront

| Method | Path | Name | Auth | Status |
| --- | --- | --- | --- | --- |
| GET | `/` | shop.index | No | Live. Loads **all** active products. |
| GET | `/c/{category:slug}` | shop.category | No | Live. Category listing, same controller. |
| GET | `/products/{product}` | products.show | No | Live. **ID-based**. 404 if inactive or category hidden. |
| POST | `/reviews` | reviews.store | Optional | Live. Throttled `reviews`. Moderation pending. |
| GET | `/contact` | contact.show | No | Live |
| POST | `/contact` | contact.store | No | Live. JSON or redirect. **CSRF currently exempt — fix.** |
| GET | `/policies/shipping` | policies.shipping | No | Live |
| GET | `/policies/returns` | policies.returns | No | Live |
| GET | `/policies/privacy` | policies.privacy | No | Live |
| GET | `/policies/terms` | policies.terms | No | Live |
| GET | `/sitemap.xml` | sitemap | No | Live. Active products + categories only. |
| GET | `/robots.txt` | — | No | Static file |

### V1 URL targets (add, then 301 old paths)

| Canonical | Redirect from |
| --- | --- |
| `/product/{slug}` | `/products/{id}` |
| `/category/{slug}` | `/c/{slug}` |
| `/about`, `/faq`, `/policies/cancellation` | — |

---

## Customer authentication

Guest middleware group.

| Method | Path | Name | Notes |
| --- | --- | --- | --- |
| GET/POST | `/login` | login / login.submit | Throttle `login` 5/min per email+IP |
| GET/POST | `/register` | register / register.submit | Strong password; terms required |
| GET/POST | `/forgot-password` | password.request / password.email | Generic success message |
| GET/POST | `/reset-password/{token}` | password.reset / password.update | Throttle `password-reset` |
| POST | `/logout` | logout | Auth required |

### Authenticated account

| Method | Path | Name | Notes |
| --- | --- | --- | --- |
| GET/PUT | `/account` | account / account.profile | |
| POST | `/account/password` | account.password | Throttle `password-change` |
| GET | `/email/verify` | verification.notice | |
| GET | `/email/verify/{id}/{hash}` | verification.verify | Signed URL |
| POST | `/email/verification-notification` | verification.send | Throttle `verification` |
| GET/POST | `/account/addresses` | addresses.index / store | |
| PUT/DELETE | `/account/addresses/{address}` | update / destroy | Owner check → 404 |
| PATCH | `/account/addresses/{address}/default` | addresses.default | |

**Missing for V1:** after login/register from checkout, `intended` must be checkout (today guests never hit checkout, so `intended` cannot be checkout). Register currently always goes to `verification.notice`.

---

## Cart (current vs V1)

**Current:** no HTTP cart. `localStorage['seeds_bazar_cart']`. Checkout posts a JSON array.

**V1 web JSON endpoints** (session + optional guest cookie `cart_token`):

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/cart` | Cart page / JSON summary |
| POST | `/cart/items` | Add `{product_id, quantity}` |
| PATCH | `/cart/items/{item}` | Update quantity |
| DELETE | `/cart/items/{item}` | Remove line |
| DELETE | `/cart` | Clear |

Server must reject: quantity &lt; 1, inactive/deleted products, quantity &gt; stock.

---

## Checkout and orders (current)

All currently `auth` middleware.

| Method | Path | Name | Notes |
| --- | --- | --- | --- |
| GET | `/checkout` | checkout | Address list. Empty if no addresses. |
| POST | `/checkout/quote` | checkout.quote | JSON `{subtotal, shipping, total, method, estimate}`. Body: `address_id`, `cart[]`. |
| POST | `/checkout` | checkout.store | Creates order. `payment_method` in `cod,online`. COD → order show. Online → Razorpay. |
| GET | `/orders` | orders.index | Paginated |
| GET | `/orders/{order}` | orders.show | Owner only (404) |
| POST | `/orders/{order}/cancel` | orders.cancel | pending/confirmed/processing |
| GET | `/orders/{order}/payment` | payments.show | Online only |
| POST | `/orders/{order}/payment/verify` | payments.verify | |
| POST | `/orders/{order}/payment/fail` | payments.fail | |

### Checkout request body (current)

```json
{
  "address_id": 1,
  "payment_method": "cod",
  "cart": [{"id": 12, "quantity": 2}]
}
```

Client `price` is ignored. Tests send `"price": 1` and assert server totals.

### V1 checkout changes

- `GET /checkout` public: if guest, show Login / Create account / Continue as guest.
- Guest POST accepts name, mobile, email, address fields (same validation as AddressRequest) instead of `address_id`.
- `payment_method` fixed to `cod` (online hidden). Razorpay routes remain for configured environments but are not a V1 launch path.
- Success: dedicated order-success page plus `orders.show`.
- Guest tracking: `GET /track` + lookup by `order_number` + phone or email (rate limited).

---

## Admin

Prefix `/admin`, names `admin.*`.

| Method | Path | Name | Status |
| --- | --- | --- | --- |
| GET/POST | `/admin/login` | admin.login | Live. **Not throttled.** |
| POST | `/admin/logout` | admin.logout | |
| GET | `/admin` | admin.dashboard | Counts + 6-month charts |
| GET/PUT | `/admin/profile` | admin.profile.* | |
| resource | `/admin/products` | except show | Paginate 8 |
| CRUD-ish | `/admin/categories` | index/store/update/toggle/placement/move/destroy | |
| GET/PUT | `/admin/settings`, logo | | |
| GET/DELETE | `/admin/contacts` | | |
| GET/PATCH/DELETE | `/admin/reviews` | | |
| GET | `/admin/orders`, `/admin/orders/{order}` | | |
| PATCH | `/admin/orders/{order}` | status | Any of `Order::STATUSES` except un-cancel |
| POST | `/admin/orders/{order}/cancel` | | |

### V1 admin additions

- `/admin/customers`
- `/admin/inventory` and low-stock
- `/admin/shipments`
- Order status restricted to legal transitions
- Inventory adjustments write `inventory_logs`

A customer session must never pass `admin` middleware (already 403).

---

## Planned internal jobs (not HTTP)

| Job | Trigger |
| --- | --- |
| CreateShiprocketShipment | Order committed, shipment pending |
| SendOrderNotification | Domain events: placed, confirmed, shipped, delivered, cancelled |
| SyncShipmentTracking | Schedule or webhook |

Shiprocket credentials stay server-side. No browser calls to Shiprocket.

### Shiprocket webhook (V1)

`POST /webhooks/shiprocket` — verify signature/token from env, CSRF exempt **this route only**, update shipment + order status via the status service. Rate limit by IP.

---

## Error behaviour

| Case | Current | V1 |
| --- | --- | --- |
| Inactive product in cart | OutOfStockException → checkout error | Same, plus cart line removed/flagged |
| Oversell race | Transaction rollback, no order | Same |
| Unserviceable pincode | HTTP 422 | Validation error, stay on checkout |
| Other customer's order | 404 | 404 |
| Non-admin hits /admin | 403 | 403 |
| Gateway down (online) | Order exists, payment failed message | N/A for COD V1; Shiprocket failure must **not** lose the order |

---

## Rate limits (current)

Defined in `AppServiceProvider`:

- `login` — 5/min email+IP
- `password-reset` — 3/min
- `password-change` — 5/min
- `verification` — 3/min
- `reviews` — 5/min

Add for V1: `admin-login`, `checkout`, `contact`, `order-track`, `webhooks`.

---

## Authentication for a future API

Not in V1. When needed: Laravel Sanctum token for mobile, same domain services, no parallel order writer. Web checkout remains the source of truth until an API order command reuses `OrderService`.
