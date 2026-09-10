# SeedPlanta database

MySQL is the system of record. Laravel 12 migrations in `database/migrations/` are the source of truth for the current schema. This document records **what exists**, **what V1 must add**, and **what not to add yet**.

All V1 work is **additive**. Do not drop live commerce tables. Do not rebuild the schema from scratch.

---

## Current schema (as migrated)

### users

| Column | Type | Notes |
| --- | --- | --- |
| id | bigint PK | |
| name | string | Combined display name |
| first_name, last_name | string(100), nullable | Added for customer auth |
| email | string, unique | Normalized lowercase on register/login |
| mobile | string(30), unique, nullable | |
| email_verified_at | timestamp, nullable | `MustVerifyEmail` |
| terms_accepted_at | timestamp, nullable | |
| password | string | Hashed via cast |
| is_admin | boolean, default false | Sole authorization flag |
| remember_token | string | |
| timestamps | | |

Related Laravel tables: `password_reset_tokens`, `sessions`.

**Issues:** `is_admin` is on the customer table (acceptable for V1). `is_admin` is currently **fillable**. No soft deletes. Deleting a user **cascades to orders and addresses**.

### categories

| Column | Type | Notes |
| --- | --- | --- |
| id | bigint PK | |
| parent_id | FK categories, nullable, nullOnDelete | Max depth 3 in code |
| name | string | |
| menu_label | string(100), nullable | Nav label |
| emoji | string(16), nullable | |
| description | text, nullable | Used as listing intro / meta fallback |
| slug | string, **unique** | Public `/c/{slug}` |
| sort_order | unsignedSmallInteger | |
| is_active | boolean | Hidden if any ancestor is inactive |
| show_in_header | boolean | |
| show_on_home | boolean | |
| image | string, nullable | Public disk path |
| timestamps | | |

Indexes: unique slug, FK parent. No composite storefront index.

### products

| Column | Type | Notes |
| --- | --- | --- |
| id | bigint PK | **Public URL uses this ID** |
| category_id | FK categories, cascadeOnDelete | Deleting a category deletes products |
| name | string | No unique slug |
| description | text, nullable | |
| sowing_season, sunlight, germination_days, harvest_days, plant_spacing, sowing_depth, growing_difficulty | string, nullable | Cultivation copy |
| price | decimal(10,2) | Selling price only |
| stock_quantity | unsignedInteger, default 0 | Source of inventory |
| unit | string | Pack size label, not a variant SKU |
| emoji | string(16) | Fallback visual |
| image | string, nullable | Single image, public disk |
| is_active | boolean | |
| timestamps | | |
| index (is_active, stock_quantity) | | |

**Missing for V1:** slug, sku, mrp, seo_title, meta_description, soft deletes, gallery, variants.

### addresses

Owned by `user_id` (required). Fields: full_name, phone, address_line_1/2, landmark, city, state, postal_code, country (default India), is_default. Index `(user_id, is_default)`.

Guests cannot persist an address today. Checkout copies address into order shipping fields.

### orders

| Column | Type | Notes |
| --- | --- | --- |
| id | bigint PK | |
| user_id | FK users, **cascadeOnDelete** | Must change — orders are historical records |
| order_number | string(30), unique | `SB-ymd-RANDOM`, not sequential |
| status | string(30), indexed | pending, confirmed, processing, shipped, delivered, cancelled |
| payment_status | string(30) | pending, paid, failed |
| payment_method | string(30) | `cod` or `online` |
| payment_gateway, gateway_order_id (unique), gateway_payment_id (unique), gateway_signature, paid_at, payment_failure_reason | | Razorpay |
| stock_deducted_at | timestamp, nullable | Idempotent deduct flag |
| shipping_name, shipping_phone | | Snapshot |
| shipping_address | text | Single concatenated string |
| shipping_method, delivery_estimate | | From settings at order time |
| subtotal, discount, shipping_charge, tax, total | decimal(12,2) | discount/tax unused in checkout |
| timestamps | | |
| index (user_id, created_at) | | |

**Missing:** guest identity, structured address (city/state/pincode), email, status history, shipment FK, sequential number.

### order_items

| Column | Type | Notes |
| --- | --- | --- |
| order_id | FK orders, cascadeOnDelete | |
| product_id | FK products, **nullOnDelete** | Good — name snapshot remains |
| product_name | string | Snapshot |
| unit | string, nullable | Snapshot |
| unit_price | decimal(12,2) | Snapshot of live price |
| quantity | unsignedInteger | |
| line_total | decimal(12,2) | |
| timestamps | | |

**Missing:** SKU snapshot, MRP/discount snapshot, image snapshot (optional).

### reviews

product_id (cascade), optional user_id (later migration), name, rating, comment, status (pending/approved/rejected). Indexed `(product_id, created_at)`.

### contact_messages

name, mobile, email, address, query, ip_address, user_agent, timestamps.

### settings

key (string PK), value. Used for tagline, WhatsApp, shipping, policies, header copy, logo path. Request-level static cache in the model, not Redis.

### Laravel infrastructure

`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`.

---

## Relationship diagram (current)

```
users 1──* addresses
users 1──* orders  1──* order_items *──0..1 products
users 1──* reviews (optional)
categories 1──* categories (parent)
categories 1──* products 1──* reviews
products.category_id cascade delete  ← risk
users.orders cascade delete          ← risk
```

---

## Historical accuracy — current vs required

| Event | Today | V1 requirement |
| --- | --- | --- |
| Product price changes | Order item `unit_price` / `line_total` already snapshotted | Keep; never display live product price on past orders |
| Product name changes | `product_name` snapshotted | Keep; add `sku` snapshot |
| Product deleted | `product_id` nulls; name remains | Prefer **soft delete** so admin can still inspect; public 404 |
| Product deactivated | Already 404 on storefront; past orders unaffected | Keep |
| Discount changes | No real discount field; order `discount` column unused | Snapshot line discount = mrp - price if MRP exists |
| User deleted | **Orders deleted** | Restrict / soft-delete users; `nullOnDelete` or restrict on `orders.user_id` |
| Category deleted | **Products deleted** | Restrict or soft-delete; do not cascade to products that were ordered |

---

## Target V1 schema (additive)

### products — new columns

```
sku              varchar(64) unique not null
slug             varchar(191) unique not null
mrp              decimal(12,2) nullable  -- compare-at; selling price remains `price`
seo_title        varchar(191) nullable
meta_description varchar(320) nullable
deleted_at       timestamp nullable
index (slug), index (sku), index (is_active, deleted_at, category_id)
```

Backfill: slug from name (`Str::slug` + uniqueness), SKU from `SP-` + zero-padded id.

### product_images (new)

```
id, product_id FK restrict
path, disk (default public)
alt, sort_order
is_primary boolean
width, height nullable
timestamps
```

Existing `products.image` remains until a backfill copies it to `product_images` as primary.

### carts / cart_items (new)

```
carts:
  id
  user_id nullable unique  -- authenticated cart
  guest_token char(64) nullable unique  -- cookie for guests
  timestamps
  check: user_id or guest_token not both empty

cart_items:
  id
  cart_id FK cascade
  product_id FK restrict
  quantity unsigned int
  timestamps
  unique (cart_id, product_id)
```

**Merge rule (canonical):**

1. On login/register, load guest cart by cookie token and user cart by `user_id`.
2. For each product: **sum quantities**.
3. Cap at current sellable stock (`is_active`, not deleted, `stock_quantity`).
4. If stock is 0, drop the line.
5. Delete the guest cart; keep the user cart.
6. Selling price is never stored on cart lines; always read from products at render and at checkout.

### inventory_logs (new)

```
id
product_id FK restrict
change_type  enum/string: initial, order, cancellation, return, adjustment, restock
quantity     signed int   -- negative for deductions
before_stock unsigned int
after_stock  unsigned int
reference_type nullable (order, admin, etc.)
reference_id nullable
created_by nullable FK users
timestamps (created_at sufficient)
index (product_id, created_at)
index (reference_type, reference_id)
```

Stock **quantity on hand** stays on `products.stock_quantity`. A separate `inventory` table is not required while there are no warehouses or variants.

Every `decrement` / `increment` / admin stock edit must write a log in the same transaction.

### orders — changes

```
user_id              nullable, change FK to restrict or nullOnDelete (not cascade)
guest_email          nullable
guest_phone          nullable
shipping_email       nullable
shipping_line_1, shipping_line_2, shipping_landmark
shipping_city, shipping_state, shipping_postal_code, shipping_country
-- keep shipping_address text as a formatted copy for display
```

Order number: unique sequential `SP` + 6 digits starting at `SP100001`. Implement with a dedicated `order_sequences` row locked inside the order transaction, not `max(id)`, to stay gap-tolerant and race-safe.

### order_items — new snapshot columns

```
sku          varchar(64) nullable
mrp          decimal(12,2) nullable
discount     decimal(12,2) default 0
```

### order_status_histories (new)

```
id, order_id FK cascade
from_status nullable
to_status not null
note nullable
created_by nullable FK users
timestamps
index (order_id, created_at)
```

Allowed transitions (V1):

```
pending     → confirmed, cancelled
confirmed   → processing, cancelled
processing  → shipped, cancelled
shipped     → out_for_delivery, delivered, rto, cancelled (cancel only if business allows; code today forbids cancel after shipped)
out_for_delivery → delivered, rto
delivered   → returned (admin)
rto         → processing, returned, cancelled
returned    → (terminal for V1)
cancelled   → none
```

Customer cancel remains: pending, confirmed, processing only.

### shipments / shipment_tracking_events (new)

```
shipments:
  id
  order_id FK unique or 1-n if splits later; V1 one shipment per order
  status: pending, created, cancelled, failed
  provider: shiprocket
  provider_shipment_id, awb, label_url nullable
  payload_json / last_error nullable
  attempts unsigned int
  last_attempt_at, shipped_at nullable
  timestamps

shipment_tracking_events:
  id, shipment_id FK
  status, location, description, tracked_at
  raw_payload json nullable
  timestamps
```

Create `shipments.status = pending` in the same order transaction. The Shiprocket HTTP call happens **after commit** in a job.

### notifications

Use Laravel's `notifications` table if in-app history is wanted. Mail can be sent from listeners without it. Do not invent a parallel notifications schema.

### contact_messages

Keep as-is. Optional: `read_at`.

---

## Intentionally deferred

| Table | Reason |
| --- | --- |
| product_variants | No current product has multiple SKUs. `unit` is a label. Adding variants now forces cart/order/inventory rewrites for zero business value. |
| admins / roles | One `is_admin` flag is enough for V1 operators. Add `roles` + `role_user` when a second permission (e.g. packing staff) is real. |
| inventory (separate) | Duplicates `products.stock_quantity`. |
| coupons / wallets | Non-goals. |
| Elasticsearch indexes | MySQL `LIKE` / `FULLTEXT` on name+description is enough until catalogue size proves otherwise. |

---

## Index plan (V1)

| Table | Index |
| --- | --- |
| products | unique slug, unique sku, (is_active, deleted_at), (category_id, is_active), FULLTEXT(name, description) optional |
| categories | unique slug, (parent_id, sort_order), (is_active, show_in_header) |
| carts | unique user_id, unique guest_token |
| cart_items | unique (cart_id, product_id) |
| orders | unique order_number, (user_id, created_at), (status, created_at), (guest_email), (guest_phone) |
| inventory_logs | (product_id, created_at), (reference_type, reference_id) |
| shipments | (status, updated_at), unique order_id (V1) |

---

## Money and types

- Prices, MRP, totals: `decimal(12,2)`. Never float columns.
- Quantities: unsigned integers. Reject 0 and negative in validation.
- PHP: use string/decimal casts already on Order/Product; compute with `bcmul`/`round(..., 2)` consistently in one pricing helper later if line math grows.

---

## N+1 and query rules

Today `ShopController::catalogProducts()` eager-loads `category.parent.parent` and `reviews`, then `get()`s **all** products. That is not N+1; it is an unbounded catalogue load.

V1 rules:

- Listings: `paginate()` with `with('category:id,name,slug,parent_id')` and a **review aggregate** (subquery or cached `reviews_avg_rating` / `reviews_count` columns), not a collection of all review rows.
- Homepage: featured/limited products only, not the full catalogue.
- Orders: always `with('items')` when rendering; never lazy-load items in a loop.
- Category depth helpers must use eager `parent.parent` (already done in several paths). `descendantAndSelfIds()` is recursive queries — acceptable for depth 3; cache per request if used on listings.

---

## Migration strategy

1. New tables first (`carts`, `inventory_logs`, `shipments`, `order_status_histories`, `product_images`).
2. Additive columns on `products` / `orders` / `order_items`.
3. Data backfill (slug, sku, primary image row, formatted address split if needed).
4. Change `orders.user_id` FK from cascade to restrict/null (requires drop + recreate FK).
5. Change `products.category_id` from cascade to restrict (or soft-delete categories).
6. Keep old columns until code is switched (`products.image`, `orders.shipping_address`).

Never deploy a migration that rewrites historical `order_items` prices.
