# Product: ClaimKH — Auto Insurance Claims SaaS for Cambodia

## What It Is
B2B2C SaaS. Insurance companies (payers) deploy it. Cambodian drivers/claimants (end users) use it.
Solves: slow, opaque, paper-heavy auto insurance claims process in Cambodia.

## Who Pays
Insurance companies (B2B customers). Licensed per seat or % claim volume.

## Who Uses
- Claimants: Cambodian drivers filing collision, theft, or injury claims (mobile-first, Khmer + EN)
- Claims adjusters: Insurance company staff reviewing/approving claims
- Admins: Insurance company ops team managing policies and SLAs

## Core Problem
Cambodia auto insurance claims are manual:
- No digital submission → paper forms, fax, in-person
- No status visibility → claimants call repeatedly
- Slow adjuster review → average 14-30 days
- High rejection rate → missing docs, unclear requirements
- Fraud-prone → no photo/GPS verification at incident scene

## Solution
Digital claim lifecycle: Submit → Document → Assign → Adjudicate → Payout
- Mobile PWA for claimants (offline-capable, Khmer UI)
- Web dashboard for adjusters/admins
- SMS notifications (Cambodian carriers)
- Document upload with AI-assisted completeness check
- GPS-tagged incident reports
- Real-time claim status tracking

## Tech Stack
- Backend: Laravel 13 (PHP 8.3)
- Frontend: Livewire + Alpine.js (Blade) or Inertia + Vue 3
- DB: MySQL 8 / PostgreSQL
- Storage: S3-compatible (Wasabi or AWS)
- Queue: Redis + Laravel Horizon
- Notifications: SMS via Vonage/Twilio (KH carriers)
- Auth: Laravel Sanctum + OTP (phone-based, no email dependency)
- i18n: Khmer (km) + English (en)

## Market Context
- Cambodia: ~4M registered vehicles (2024), growing 8%/yr
- Insurance penetration low but mandated for vehicles
- Mobile internet penetration: 85%+, smartphone-first
- ABA Bank, ACLEDA, Wing dominate digital payments → integrate for payout
