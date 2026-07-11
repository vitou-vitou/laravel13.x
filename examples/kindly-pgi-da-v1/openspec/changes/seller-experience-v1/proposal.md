## Why

Vendors can confirm/ship/deliver but cannot manage catalog or see rich order context. Phase 2 makes **seller-side** usable without Filament.

## What Changes

- Vendor **product CRUD** (create/edit product + variants, stock, status draft/active)
- Vendor **order detail** page (lines, customer shipping snapshot, action buttons)
- Basic **inventory** warnings on dashboard (low stock badge)

## Non-goals

- Bulk CSV import
- Seller analytics dashboard
- In-app buyer chat

## Depends on

- Archive `buyer-experience-v1` (shipping snapshot on orders)
