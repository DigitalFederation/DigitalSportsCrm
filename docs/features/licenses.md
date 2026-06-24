---
title: Licenses
description: License purchase, validation flow, TD approval, and state machine
---

# License System

## Overview

The license system enables entities and individuals to request and purchase licenses directly, with simplified state management and federation oversight.

---

## 1. License States

Licenses go through the following states:

| State | State Class | Description |
|-------|-------------|-------------|
| **Pending TD Approval** | `PendingTechnicalDirectorApprovalLicenseAttributedState` | Diving license (entity only) awaiting Technical Director approval |
| **Pending Validation** | `PendingValidationLicenseAttributedState` | License awaiting admin/federation validation |
| **Waiting Approval** | `WaitingApprovalLicenseAttributedState` | License awaiting approval within the federation/admin validation workflow |
| **Pending** | `PendingLicenseAttributedState` | License approved, awaiting payment |
| **Provisional** | `ProvisionalLicenseAttributedState` | Temporarily valid state used in certain purchase flows (e.g. group/bulk purchases) before the license becomes fully Active |
| **Active** | `ActiveLicenseAttributedState` | Payment confirmed, license is valid |
| **Suspended** | `SuspendedLicenseAttributedState` | Temporarily disabled by administrator |
| **Expired** | `ExpiredLicenseAttributedState` | License has passed its expiration date |
| **Canceled** | `CanceledLicenseAttributedState` | License was rejected or canceled |

---

## 2. Initial State Rules

When a license is purchased, the initial state depends on:

The decision is driven by several properties: the purchaser type, whether the license is free, the license's `requires_admin_validation` flag, the license committee, and whether that committee is international (`committee.isInternational()`). In the diving example deployment, `DIVING` is an international committee while `DIVINGSERVICES` is non-international.

| Purchaser | License | Requires Validation | Free | Initial State |
|-----------|---------|---------------------|------|---------------|
| Entity | Non-international diving (`DIVINGSERVICES`) | Yes | - | Pending TD Approval |
| Any | Non-international, non-diving | Yes | - | Pending Validation |
| Any | International committee (e.g. `DIVING`) | Yes | No | Pending (awaiting payment) |
| Any | Any | No | Yes | Active |
| Any | Any | No | No | Pending (awaiting payment) |

**Important:**
- **TD approval** applies **only** to entities purchasing **non-international** diving (`DIVINGSERVICES`) licenses.
- **International** licenses (e.g. `DIVING`) skip **both** TD approval **and** admin validation regardless of the `requires_admin_validation` flag — they go straight to payment (or to Active if free).
- Non-international, non-diving licenses with validation enabled go to federation (admin) validation.

---

## 3. License Validity

### Configuration Options

- **Interval**: Duration number (e.g., 1, 2, 3)
- **Interval Unit**: weeks, months, or years
- **Validity Type**: fixed_duration or calendar_year

### Validity Rules

**Perpetual License:**
- No interval defined
- License never expires

**Fixed-Duration License:**
- Valid for exact interval from activation date
- Example: 1-year license activated March 15, 2025 → expires March 15, 2026

**Calendar Year License:**
- Expires on December 31st of the target year
- Example: 1-year license activated March 15, 2025 → expires December 31, 2025

---

## 4. Requirements

### Document Requirements

Licenses can require official documents before purchase:
- Documents must be in "Active" state
- Documents must not be expired
- Purchase is blocked if requirements not met

### Certification Requirements

Licenses can require certifications before purchase:
- Only applies to individual purchasers (entities exempt)
- The individual must hold **at least one** of the required certifications (OR logic) — e.g. a coach with Grade I OR Grade II OR Grade III qualifies
- Active and provisional certifications both count
- Clear error message if requirements not met

---

## 5. Diving License Workflow (Example Deployment)

In the diving example deployment, licenses split into two committees that behave differently: **non-international** diving services (`DIVINGSERVICES`) and the **international** committee (`DIVING`).

### Non-International Diving (`DIVINGSERVICES`)

| Purchaser | Flow |
|-----------|------|
| **Entity** | Request → TD Approval → Federation Validation → Payment → Active |
| **Individual** | Request → Federation Validation → Payment → Active |

(Both flows assume the license has `requires_admin_validation` enabled.)

### International Diving (`DIVING`)

International licenses skip **both** TD approval and federation validation:

| Purchaser | Flow |
|-----------|------|
| **Entity / Individual** | Request → Payment → Active (or directly Active if free) |

### Entity Non-International Diving Steps

1. Entity requests the diving license
2. **TD Approval**: All assigned technical directors must approve
3. **Federation Validation**: Admin reviews and approves/rejects
4. **Payment**: If approved and the license is paid, awaits payment
5. **Active**: Payment confirmed, license is valid

### Validation Rules

- **TD Approval**: All assigned TDs must approve before federation review
- **Admin Approval**: Optional notes (max 500 characters)
- **Admin Rejection**: Reason required (max 500 characters), visible to entity

---

## 6. Purchase Types

### Direct Purchase
Individual or entity purchases license for themselves.

### Group Purchase
Entity purchases licenses for multiple members at once.

- Tracks which entity requested the license
- Members receive individual licenses
- Entity can manage all purchased licenses
