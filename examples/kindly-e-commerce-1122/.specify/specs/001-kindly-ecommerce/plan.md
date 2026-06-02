# Implementation Plan: Kindly E-Commerce

**Branch**: `001-kindly-ecommerce` | **Date**: 2026-06-01 | **Spec**: [spec.md](./spec.md)

## Summary

Laravel 13 + **Breeze (Blade)** for auth; session cart; SQLite models for `products`, `orders`, `order_items`. Arena.ai reviews via `docs/ARENA_LOOP.md`.

## Technical Context

**Stack**: PHP 8.3, Laravel 13, Breeze Blade, SQLite  
**Cart**: `App\Services\CartService` (session key `cart`)  
**Checkout**: DB transaction — order + items + stock decrement  
**Payment**: Stub (`status = pending`)

## Routes

| Method | Path | Middleware |
|--------|------|------------|
| GET | `/` | guest | Shop catalog |
| GET | `/cart` | guest | Cart page |
| POST | `/cart/items` | guest | Add line |
| PATCH | `/cart/items/{product}` | guest | Update qty |
| DELETE | `/cart/items/{product}` | guest | Remove line |
| POST | `/checkout` | auth | Place order |
| GET | `/orders` | auth | Order list |
| Breeze | `/login`, `/register`, … | — | Session auth |

## Test matrix

| Test | Concern |
|------|---------|
| `ProductCatalogTest` | Shop lists products |
| `CartTest` | Add/update/remove; DB pricing |
| `CheckoutTest` | Auth gate; order + stock |
| `OrderOwnershipTest` | Cannot view others' orders |
| `KindlyEcommerceBrandingTest` | Shop branding |

## Arena integration

Prompt A/B → `docs/ARENA_REVIEW_SPEC.md`, `docs/ARENA_REVIEW_PLAN.md` (Direct + claude-sonnet-4-6).
