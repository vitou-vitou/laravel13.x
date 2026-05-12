# Filament Order Wizard — 6-Step Spec

## Overview

A `CreateOrder` Filament resource page using `Wizard` form component. Six sequential steps guide staff through creating an order end-to-end. Each step validates before advancing.

---

## Steps

| # | Slug | Label | Purpose |
|---|------|-------|---------|
| 1 | `customer` | Customer | Select or create customer |
| 2 | `items` | Items | Add order lines (product + qty + price) |
| 3 | `shipping` | Shipping | Delivery address + method |
| 4 | `payment` | Payment | Payment method + terms |
| 5 | `notes` | Notes | Internal notes + attachments |
| 6 | `review` | Review & Confirm | Read-only summary before submit |

---

## Step Detail

### Step 1 — Customer
- `customer_id` — `Select` (searchable, relationship `customers`, required)
- `is_new_customer` — `Toggle` — reveals inline sub-form when true
  - `name`, `email`, `phone` (visible only when `is_new_customer = true`)
- On new customer: create record before order save via `afterStateUpdated` or mutate before create

### Step 2 — Items
- `items` — `Repeater` (min 1, addable/removable)
  - `product_id` — `Select` (searchable, relationship `products`)
  - `quantity` — `TextInput` (numeric, min 1)
  - `unit_price` — `TextInput` (numeric, disabled, auto-filled from product)
  - `discount` — `TextInput` (numeric, optional, %)
  - `line_total` — computed placeholder (display only via `Placeholder`)
- `subtotal` — `Placeholder` computed from repeater

### Step 3 — Shipping
- `shipping_address_id` — `Select` (relationship `customer.addresses`, reactive on Step 1 customer)
- `shipping_street`, `shipping_city`, `shipping_state`, `shipping_zip`, `shipping_country` — text inputs (shown when no address selected or new)
- `shipping_method` — `Select` (options: standard, express, overnight, pickup)
- `estimated_delivery` — `DatePicker` (optional)

### Step 4 — Payment
- `payment_method` — `Select` (options: cash, bank_transfer, credit_card, invoice)
- `payment_terms` — `Select` (options: immediate, net_15, net_30, net_60) — shown when `payment_method = invoice`
- `due_date` — `DatePicker` — shown when `payment_method = invoice`
- `currency` — `Select` (ISO codes, default USD)
- `tax_rate` — `TextInput` (numeric %, default 0)

### Step 5 — Notes
- `internal_notes` — `Textarea` (optional)
- `customer_notes` — `Textarea` (optional, printed on invoice)
- `attachments` — `FileUpload` (multiple, disk: s3/local, accepted: pdf/png/jpg)
- `priority` — `Select` (options: normal, high, urgent)
- `tags` — `TagsInput` (optional)

### Step 6 — Review & Confirm
- All fields rendered as `Placeholder` (read-only summary)
- Show computed: subtotal, tax, shipping cost, **total**
- `confirm` — `Checkbox` ("I confirm this order is correct", required)
- Submit triggers `CreateOrder` action

---

## Model: `Order`

```
orders
  id
  customer_id        FK customers
  status             enum: draft|confirmed|processing|shipped|delivered|cancelled
  subtotal           decimal(10,2)
  tax_amount         decimal(10,2)
  shipping_cost      decimal(10,2)
  total              decimal(10,2)
  currency           char(3)
  payment_method     string
  payment_terms      string nullable
  due_date           date nullable
  shipping_method    string
  shipping_address   json
  estimated_delivery date nullable
  internal_notes     text nullable
  customer_notes     text nullable
  priority           string default normal
  confirmed_at       timestamp nullable
  timestamps

order_items
  id
  order_id           FK orders
  product_id         FK products
  quantity           int
  unit_price         decimal(10,2)
  discount           decimal(5,2) default 0
  line_total         decimal(10,2)
  timestamps
```

---

## File Structure

```
app/
  Filament/
    Resources/
      OrderResource.php
      OrderResource/
        Pages/
          CreateOrder.php      ← wizard lives here
          ListOrders.php
          EditOrder.php
        RelationManagers/
          ItemsRelationManager.php
  Models/
    Order.php
    OrderItem.php
  Policies/
    OrderPolicy.php
database/
  migrations/
    xxxx_create_orders_table.php
    xxxx_create_order_items_table.php
```

---

## Validation Rules

| Field | Rule |
|-------|------|
| `customer_id` | required, exists:customers,id |
| `items` | required, array, min:1 |
| `items.*.product_id` | required, exists:products,id |
| `items.*.quantity` | required, integer, min:1 |
| `items.*.unit_price` | required, numeric, min:0 |
| `shipping_method` | required, in:standard,express,overnight,pickup |
| `payment_method` | required |
| `confirm` (step 6) | accepted |

---

## Computed Totals Logic

```php
// In mutateFormDataBeforeCreate()
$subtotal = collect($data['items'])->sum(fn($i) =>
    $i['unit_price'] * $i['quantity'] * (1 - ($i['discount'] ?? 0) / 100)
);
$tax = $subtotal * ($data['tax_rate'] / 100);
$data['subtotal']   = $subtotal;
$data['tax_amount'] = $tax;
$data['total']      = $subtotal + $tax + ($data['shipping_cost'] ?? 0);
```

---

## Lifecycle Hooks

| Hook | Action |
|------|--------|
| `afterStateUpdated` on `product_id` | fill `unit_price` from product |
| `afterStateUpdated` on `customer_id` | reload `shipping_address_id` options |
| `mutateFormDataBeforeCreate` | compute totals, set `status = confirmed`, set `confirmed_at` |
| `afterCreate` | fire `OrderCreated` event, send confirmation email |

---

## Permissions (Policy)

| Ability | Role |
|---------|------|
| viewAny | admin, sales |
| create | admin, sales |
| update | admin, sales (own orders only) |
| delete | admin |
| confirm | admin |
