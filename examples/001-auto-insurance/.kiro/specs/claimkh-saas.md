# Spec: ClaimKH — Auto Insurance Claims SaaS

**Author:** team-lead  
**Date:** 2026-07-26  
**Status:** Approved  
**Reviewers:** product-owner, tech-lead  
**Version:** 1.0.0

---

## 1. Context

Cambodia auto insurance claims are fully manual: paper forms, fax, in-person visits. Average claim resolution = 14–30 days. ~4M registered vehicles, 85%+ smartphone penetration, yet zero digital claims infrastructure exists at scale.

**Who pays:** Insurance companies (B2B). Licensed per seat or % claim volume.  
**Who uses:** Claimants (Cambodian drivers), Claims adjusters, Admins.  
**Core pain:** No digital submission, no status visibility, high rejection from missing docs, fraud-prone with no GPS/photo verification.

**Stack:** Laravel 13 (PHP 8.3), Livewire + Alpine.js (Blade), MySQL 8, S3-compatible storage (Wasabi/AWS), Redis + Laravel Horizon, SMS via Vonage/Twilio, Laravel Sanctum + OTP (phone-based), i18n: Khmer (km) + English (en).

---

## 2. Functional Requirements

### FR-1: OTP Phone Authentication
FR-1.1 System MUST allow claimants to register/login via phone number + SMS OTP only — no email required.  
FR-1.2 System MUST support Cambodian phone numbers (+855 prefix, 8-9 digits).  
FR-1.3 OTP MUST expire after 5 minutes.  
FR-1.4 System MUST rate-limit OTP requests to 3 per phone per 10 minutes.  
FR-1.5 Adjusters and admins MUST authenticate via phone OTP or web-based session with 2FA.

### FR-2: Claim Submission (Claimant)
FR-2.1 Claimant MUST be able to submit a new claim with: claim type (collision/theft/injury), incident date/time, incident location (GPS coordinates or manual address), description.  
FR-2.2 System MUST support photo upload (min 1, max 20 photos per claim, JPEG/PNG/HEIC, max 10MB each).  
FR-2.3 System MUST capture GPS coordinates at time of incident report submission.  
FR-2.4 System MUST support offline-capable PWA — claimant can fill form offline, submit when online.  
FR-2.5 UI MUST be available in Khmer (km) and English (en), defaulting to Khmer.  
FR-2.6 System MUST generate a unique claim reference number upon successful submission.

### FR-3: Document Management
FR-3.1 System MUST allow upload of supporting documents: driver's license, vehicle registration, police report, repair estimate (PDF/JPEG/PNG, max 20MB each).  
FR-3.2 System MUST perform AI-assisted completeness check — flag missing required docs per claim type.  
FR-3.3 Documents MUST be stored in S3-compatible storage with signed URL access only.  
FR-3.4 System SHOULD auto-classify uploaded documents by type using file metadata + AI inference.

### FR-4: Claim Status Tracking (Claimant)
FR-4.1 Claimant MUST be able to view real-time claim status: Submitted → Under Review → Info Requested → Approved → Rejected → Paid.  
FR-4.2 System MUST send SMS notification on every status change.  
FR-4.3 Claimant MUST see estimated resolution date based on SLA configuration.  
FR-4.4 Claimant MUST be able to respond to info requests by uploading additional documents.

### FR-5: Adjuster Dashboard
FR-5.1 Adjuster MUST see a queue of assigned claims sorted by SLA deadline (ascending).  
FR-5.2 Adjuster MUST be able to: approve, reject, or request additional info on a claim.  
FR-5.3 Adjuster MUST be able to add internal notes (not visible to claimant).  
FR-5.4 Adjuster MUST see GPS location of incident on embedded map.  
FR-5.5 Adjuster MUST be able to view all uploaded photos/documents inline.  
FR-5.6 System MUST log all adjuster actions with timestamp and user ID.

### FR-6: Admin Panel
FR-6.1 Admin MUST be able to create/manage insurance company tenants (multi-tenant).  
FR-6.2 Admin MUST be able to configure SLA rules per claim type (e.g., collision = 7 days, theft = 14 days).  
FR-6.3 Admin MUST be able to assign/reassign claims to adjusters.  
FR-6.4 Admin MUST be able to view aggregate reports: claims by status, by type, avg resolution time, rejection rate.  
FR-6.5 Admin MUST be able to manage adjuster accounts (create, deactivate, set permissions).  
FR-6.6 Admin MUST be able to configure required document checklist per claim type.

### FR-7: Multi-Tenancy
FR-7.1 System MUST support multiple insurance company tenants on a single deployment.  
FR-7.2 Each tenant's data MUST be fully isolated — no cross-tenant data access.  
FR-7.3 Tenant MUST be identifiable by subdomain (e.g., `aia.claimkh.com`) or tenant_id in API.

### FR-8: Payout Integration (Phase 2)
FR-8.1 System SHOULD support payout initiation via ABA Bank, ACLEDA, Wing APIs.  
FR-8.2 Payout status MUST be tracked and reflected in claim status.  
FR-8.3 System MUST NOT store raw bank credentials — use tokenized payment references only.

---

## 3. Non-Functional Requirements

NFR-1 **Performance:** API endpoints MUST respond in < 500ms at p95 under 200 concurrent users.  
NFR-2 **Availability:** System MUST target 99.5% uptime (SLA to insurance company clients).  
NFR-3 **Security:** All data MUST be encrypted in transit (TLS 1.2+) and at rest. PII fields encrypted in DB.  
NFR-4 **Accessibility:** Claimant PWA MUST meet WCAG 2.1 AA. Mobile-first, touch targets ≥ 44px.  
NFR-5 **Offline:** Claimant PWA MUST support claim form completion offline with sync on reconnect.  
NFR-6 **i18n:** All claimant-facing strings MUST be translated in km + en. Khmer default.  
NFR-7 **Scalability:** Architecture MUST support horizontal scaling (stateless app servers, queue workers).  
NFR-8 **Audit:** All claim state transitions MUST be immutably logged with actor, timestamp, old state, new state.  
NFR-9 **File Storage:** Uploaded files MUST be scanned for malware before making accessible.  
NFR-10 **SMS:** SMS delivery MUST have retry logic with max 3 attempts and DLR (delivery receipt) tracking.

---

## 4. Acceptance Criteria

### Auth
AC-1 (FR-1.1, FR-1.2) Given phone number +85512345678, When claimant requests OTP, Then SMS sent within 30s and OTP valid for 5 min.  
AC-2 (FR-1.4) Given 3 OTP requests in 10 min from same phone, When 4th request made, Then HTTP 429 returned with retry-after header.  
AC-3 (FR-1.3) Given OTP issued 6 min ago, When claimant submits OTP, Then HTTP 422 "OTP expired" returned.

### Claim Submission
AC-4 (FR-2.1, FR-2.6) Given authenticated claimant, When valid claim submitted, Then claim created with status=Submitted, unique ref generated (format: CLM-YYYYMMDD-XXXX), HTTP 201 returned.  
AC-5 (FR-2.2) Given photo > 10MB, When upload attempted, Then HTTP 422 "File too large" returned.  
AC-6 (FR-2.3) Given device with GPS, When claim form opened, Then GPS coords captured and attached to claim.  
AC-7 (FR-2.4) Given no internet connection, When claimant completes form, Then data persisted locally; When reconnected, Then auto-submitted.  
AC-8 (FR-2.5) Given user browser language = km, When app loaded, Then UI rendered in Khmer.

### Document Management
AC-9 (FR-3.2) Given collision claim missing police report, When completeness check runs, Then claim flagged with missing_docs=['police_report'].  
AC-10 (FR-3.3) Given document uploaded, When claimant requests download URL, Then signed URL returned with 1-hour expiry, not publicly accessible.

### Status Tracking
AC-11 (FR-4.1, FR-4.2) Given claim status changes to Approved, When transition saved, Then claimant receives SMS within 60s with claim ref and new status.  
AC-12 (FR-4.3) Given claim type=collision, SLA=7 days, When claim submitted, Then estimated_resolution_date = submitted_at + 7 days displayed.

### Adjuster
AC-13 (FR-5.2, NFR-8) Given adjuster approves claim, When action saved, Then claim status=Approved, audit log entry created with adjuster_id, timestamp, old_status, new_status.  
AC-14 (FR-5.1) Given adjuster has 10 assigned claims, When dashboard loaded, Then claims sorted by SLA deadline ASC, overdue claims highlighted.

### Admin
AC-15 (FR-6.2) Given admin sets collision SLA = 5 days for tenant X, When collision claim submitted under tenant X, Then estimated_resolution_date = submitted_at + 5 days.  
AC-16 (FR-7.2) Given two tenants A and B, When adjuster from tenant A queries claims, Then only tenant A claims returned — 0 results for tenant B.

---

## 5. Edge Cases

EC-1 Claimant submits claim with GPS coords (0,0) → treat as invalid GPS, store null, flag for manual address entry.  
EC-2 SMS delivery fails after 3 retries → mark notification as failed, create in-app notification, alert admin.  
EC-3 File upload interrupted mid-stream → server detects partial upload, deletes file, returns HTTP 422.  
EC-4 Claimant submits duplicate claim (same vehicle + incident date within 24h) → HTTP 409 with existing claim ref.  
EC-5 Adjuster session expires mid-review → draft notes auto-saved locally, prompt re-auth, restore state.  
EC-6 S3 storage unavailable → queue upload with retry, return HTTP 202 Accepted (async), notify when stored.  
EC-7 OTP SMS not delivered (invalid number) → HTTP 422 "Invalid phone number" with Twilio/Vonage error code.  
EC-8 Offline claim sync conflict (same claim edited online by adjuster while offline claimant reconnects) → reject offline version, notify claimant of conflict.  
EC-9 Tenant subdomain not found → HTTP 404 with branded error page.  
EC-10 Document fails malware scan → quarantine file, return HTTP 422, log security event, alert admin.

---

## 6. API Contracts

```typescript
// Auth
POST /api/v1/auth/otp/request
Body: { phone: string }  // E.164 format
Response 200: { expires_at: string }
Response 422: { error: string }
Response 429: { error: string, retry_after: number }

POST /api/v1/auth/otp/verify
Body: { phone: string, otp: string }
Response 200: { token: string, user: UserResource }
Response 422: { error: "invalid_otp" | "expired_otp" }

// Claims
POST /api/v1/claims
Headers: Authorization: Bearer {token}
Body: {
  type: "collision" | "theft" | "injury",
  incident_at: string,  // ISO 8601
  location: { lat: number | null, lng: number | null, address: string | null },
  description: string
}
Response 201: { claim: ClaimResource }
Response 409: { error: "duplicate_claim", existing_ref: string }
Response 422: { errors: Record<string, string[]> }

GET /api/v1/claims/{ref}
Response 200: { claim: ClaimResource }
Response 404: { error: "not_found" }

PATCH /api/v1/claims/{ref}/status  // Adjuster only
Body: { status: ClaimStatus, note?: string }
Response 200: { claim: ClaimResource }

// Documents
POST /api/v1/claims/{ref}/documents
Body: multipart/form-data { file: File, type: DocumentType }
Response 201: { document: DocumentResource }
Response 202: { message: "queued", job_id: string }  // S3 unavailable
Response 422: { error: string }

GET /api/v1/claims/{ref}/documents/{id}/url
Response 200: { url: string, expires_at: string }

// Types
type ClaimStatus = "submitted" | "under_review" | "info_requested" | "approved" | "rejected" | "paid"
type DocumentType = "driver_license" | "vehicle_registration" | "police_report" | "repair_estimate" | "photo" | "other"

interface ClaimResource {
  ref: string
  type: string
  status: ClaimStatus
  incident_at: string
  location: { lat: number | null, lng: number | null, address: string | null }
  description: string
  estimated_resolution_date: string
  missing_docs: DocumentType[]
  created_at: string
  updated_at: string
}

interface UserResource {
  id: number
  phone: string
  name: string | null
  role: "claimant" | "adjuster" | "admin"
  tenant_id: number
}

interface DocumentResource {
  id: number
  type: DocumentType
  filename: string
  status: "pending" | "available" | "quarantined"
  created_at: string
}
```

---

## 7. Data Models

### tenants
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint unsigned | PK, auto-increment |
| name | varchar(255) | NOT NULL |
| subdomain | varchar(100) | UNIQUE, NOT NULL |
| config | json | nullable (SLA rules, doc requirements) |
| is_active | boolean | NOT NULL, default true |
| created_at | timestamp | NOT NULL |
| updated_at | timestamp | NOT NULL |

### users
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint unsigned | PK, auto-increment |
| tenant_id | bigint unsigned | FK tenants.id, NOT NULL |
| phone | varchar(20) | NOT NULL, unique per tenant |
| name | varchar(255) | nullable |
| role | enum('claimant','adjuster','admin') | NOT NULL |
| is_active | boolean | NOT NULL, default true |
| created_at | timestamp | NOT NULL |
| updated_at | timestamp | NOT NULL |
| INDEX | tenant_id, phone | composite unique |

### otp_requests
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint unsigned | PK |
| phone | varchar(20) | NOT NULL, indexed |
| otp_hash | varchar(255) | NOT NULL (bcrypt) |
| expires_at | timestamp | NOT NULL |
| used_at | timestamp | nullable |
| created_at | timestamp | NOT NULL |

### claims
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint unsigned | PK |
| tenant_id | bigint unsigned | FK tenants.id, NOT NULL |
| ref | varchar(30) | UNIQUE, NOT NULL (CLM-YYYYMMDD-XXXX) |
| claimant_id | bigint unsigned | FK users.id, NOT NULL |
| adjuster_id | bigint unsigned | FK users.id, nullable |
| type | enum('collision','theft','injury') | NOT NULL |
| status | enum('submitted','under_review','info_requested','approved','rejected','paid') | NOT NULL, default 'submitted' |
| incident_at | timestamp | NOT NULL |
| lat | decimal(10,8) | nullable |
| lng | decimal(11,8) | nullable |
| address | text | nullable |
| description | text | NOT NULL |
| estimated_resolution_date | date | nullable |
| missing_docs | json | nullable |
| created_at | timestamp | NOT NULL |
| updated_at | timestamp | NOT NULL |
| INDEX | tenant_id, status |
| INDEX | tenant_id, adjuster_id |

### documents
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint unsigned | PK |
| claim_id | bigint unsigned | FK claims.id, NOT NULL |
| type | enum(see DocumentType) | NOT NULL |
| original_filename | varchar(255) | NOT NULL |
| storage_path | varchar(500) | NOT NULL |
| mime_type | varchar(100) | NOT NULL |
| size_bytes | int unsigned | NOT NULL |
| status | enum('pending','available','quarantined') | NOT NULL, default 'pending' |
| created_at | timestamp | NOT NULL |
| updated_at | timestamp | NOT NULL |

### claim_audit_logs
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint unsigned | PK |
| claim_id | bigint unsigned | FK claims.id, NOT NULL |
| actor_id | bigint unsigned | FK users.id, NOT NULL |
| action | varchar(100) | NOT NULL |
| old_status | varchar(50) | nullable |
| new_status | varchar(50) | nullable |
| note | text | nullable |
| created_at | timestamp | NOT NULL |
| INDEX | claim_id |

### notifications
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | FK users.id, NOT NULL |
| claim_id | bigint unsigned | FK claims.id, nullable |
| channel | enum('sms','in_app') | NOT NULL |
| message | text | NOT NULL |
| status | enum('pending','sent','failed') | NOT NULL, default 'pending' |
| attempts | tinyint unsigned | NOT NULL, default 0 |
| sent_at | timestamp | nullable |
| created_at | timestamp | NOT NULL |
| updated_at | timestamp | NOT NULL |

---

## 8. Out of Scope (v1)

OS-1 **Email authentication** — phone OTP only. Email can be added in v2 for admin portal.  
OS-2 **Payout execution** — FR-8 (payout integration) deferred to Phase 2. V1 only tracks status.  
OS-3 **AI fraud detection** — GPS + photo cross-validation deferred to Phase 2.  
OS-4 **Mobile native apps** — PWA only. iOS/Android native shells are Phase 3.  
OS-5 **Third-party adjuster portal** — all adjusters are internal to insurance company in v1.  
OS-6 **Claim appeal workflow** — rejected claims cannot be appealed in v1; resubmission only.  
OS-7 **Real-time chat** — adjuster↔claimant communication is async (document upload + status notes only).  
OS-8 **Video evidence upload** — photos only in v1. Video support in Phase 2.  
OS-9 **WhatsApp / Telegram notifications** — SMS only in v1.  
OS-10 **White-label mobile apps** — tenants share the same PWA URL in v1.

---

## 9. Implementation Phases

### Phase 1 (MVP — 8 weeks)
- [ ] FR-1: OTP Auth
- [ ] FR-2: Claim Submission (online only first, offline second)
- [ ] FR-3: Document Upload (without AI completeness check)
- [ ] FR-4: Status Tracking + SMS
- [ ] FR-5: Adjuster Dashboard (basic)
- [ ] FR-6: Admin Panel (basic)
- [ ] FR-7: Multi-tenancy

### Phase 2 (6 weeks post-MVP)
- [ ] FR-3.2: AI completeness check
- [ ] FR-8: Payout integration (ABA/ACLEDA/Wing)
- [ ] AI fraud signals
- [ ] Video upload
- [ ] Advanced analytics

### Phase 3
- [ ] Native mobile shell (iOS/Android)
- [ ] WhatsApp notifications
- [ ] Claim appeal workflow
- [ ] White-label mobile apps
