# 04: Lab & Diagnostic Report Pickup Status Tracker

**What to build:** A lightweight, non-clinical pickup readiness tracker enabling patients to check if laboratory, ultrasound, or radiology results are ready for collection at the hospital records counter without exposing medical details.

**Blocked by:** 01: Hospital Foundation & Guest FAQ Decision Tree

**Status:** done

- [x] Patient can enter a diagnostic test order number and the last 4 digits of their registered phone number to verify identity.
- [x] Patient views clear readiness status: "In Analysis", "Ready for Collection at Counter [X]", or "Already Collected".
- [x] Explicit non-clinical privacy guard: Medical test values, diagnoses, and doctor clinical notes are never exposed on this tracking view.
- [x] Hospital records staff can look up orders by reference and update readiness status and designated pickup counter.
