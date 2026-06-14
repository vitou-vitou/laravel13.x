## Why

Buyers can checkout and view orders, but the flow still feels like an admin demo: no saved addresses, no wishlist, and order status is raw enum text. Phase 1 makes the **customer journey** feel like a real shop without touching seller or payments architecture.

## What Changes

- **Shipping addresses** — CRUD on profile; default address; checkout uses selected address (stored on order snapshot)
- **Wishlist** — save/remove products; list page; add to cart from wishlist
- **Order timeline** — per vendor group: visual steps (paid → confirmed → shipped → delivered) + tracking when present

## Capabilities

### New

- `buyer-experience`: addresses, wishlist, order timeline UI

### Modified

- Checkout attaches shipping address snapshot to order
- Order detail page shows timeline instead of only `status->value`

## Non-goals

- Real carrier tracking APIs
- Guest checkout
- Email/SMS notifications (Phase 1b or later)
- Recommendations or infinite scroll
