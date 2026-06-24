---
title: Memberships
description: Affiliation plans, insurance, subscription validation, and state machines
---

# Membership & Subscriptions System

This document explains the core logic and relationships between the main membership-related entities: Affiliation Plans, Insurance Plans, Membership Packages, Subscriptions, and the policy management system.

---

## 1. Core Membership Entities

-   **`AffiliationPlan`**: Defines a plan for affiliating members (individuals or entities) to a federation. It includes pricing, duration, and type (individual/entity). A key feature is the `is_validation_plan` flag, which determines if the plan grants advanced privileges like requesting licenses or insurances.
-   **`InsurancePlan`**: Defines an insurance product that can be attached to memberships. It specifies fees, policy numbers (for group plans), and duration.
-   **`MembershipPackage`**: Bundles one or more Affiliation and Insurance Plans into a single package that members can subscribe to.
-   **`MemberSubscription`**: Represents a member's (individual or entity) subscription to a `MembershipPackage`. It tracks the start date, end date, and status.
-   **`Affiliation`**: An instance of an affiliation for a member, created as a result of a subscription.
-   **`Insurance`**: An instance of an insurance policy for a member, also created from a subscription.

### Data Flow

1.  **Federations** define `AffiliationPlan`s and `InsurancePlan`s.
2.  These plans are bundled into `MembershipPackage`s.
3.  **Members** (Individuals or Entities) subscribe to a `MembershipPackage`, which creates a `MemberSubscription`.
4.  This action instantiates the corresponding `Affiliation` and `Insurance` records for the member.

### Key Logic: Validation Plans

-   Only `AffiliationPlan`s with `is_validation_plan=true` grant members the ability to request additional services like licenses and insurances.
-   The `Domain\Memberships\Services\ValidationPlanPrivilegeService` provides helper methods to check these privileges throughout the application.

---

## 2. Insurance Policy Management

The system supports three types of insurance policy number management, configured at the `InsurancePlan` level.

### Policy Types

1.  **Group Plans**
    *   **Condition**: The `InsurancePlan` has a `policy_number` set.
    *   **Behavior**: All `Insurance` instances created from this plan automatically inherit the plan's fixed policy number. The number cannot be edited on individual subscriptions.
    *   **Use Case**: Corporate or collective policies where all members share one number.

2.  **Sequential Plans**
    *   **Condition**: The `InsurancePlan` has a `policy_number_prefix` set (and is not a group plan).
    *   **Behavior**: Automatically generates a unique, sequential policy number for each new `Insurance` subscription. It uses a configurable prefix and format (e.g., `{prefix}-{sequence}`).
    *   **Use Case**: Individual policies that require unique, automated numbering.

3.  **Manual Plans**
    *   **Condition**: Both `policy_number` and `policy_number_prefix` are null.
    *   **Behavior**: The `policy_number` on the `Insurance` instance is left null. It must be added manually by an administrator after the subscription is created.

### Policy Number Assignment

`CreateMemberSubscriptionAction` delegates insurance creation to `CreateInsuranceAction`, whose private `generatePolicyNumber()` method decides which numbering scheme to apply based on the `InsurancePlan`'s configuration. For sequential plans it calls `InsurancePlan::generateNextPolicyNumber()` to produce the next unique number.

### Official Document Requirements

-   **Functionality**: `InsurancePlan`s can be configured to require specific official documents before an **Individual** can subscribe.
-   **Configuration**: This is done via two fields on the `insurance_plans` table:
    *   `requires_official_document` (boolean)
    *   `required_document_type` (string, from `OfficialDocumentTypeEnum`)
-   **Validation**: Before an individual can subscribe to a `MembershipPackage`, the system checks all contained `InsurancePlan`s. It verifies that the user has uploaded the required documents and that they are in an "Active" state and not expired.
-   **Namespace Specific**: This validation is **only** enforced for users in the **Individual** namespace. Entity and Federation subscriptions bypass this check.
-   **UI**: Insurance plans and packages with document requirements are clearly marked with a shield icon and informational text.

---

## 3. Subscription Validation & Duplicate Prevention

The system implements sophisticated validation logic to prevent duplicate subscriptions while allowing partial plan overlap. This is handled by three key components working together.

### Key Files

- `App\Livewire\Entity\MemberSubscriptionManager` - UI filtering logic
- `Domain\Memberships\Services\SubscriptionValidationService` - Backend validation
- `Domain\Memberships\Actions\CreateAffiliationAction` - Affiliation creation with duplicate skipping

### Package Type Filtering (UI Layer)

When an entity selects a membership package, the system filters which individuals are shown based on the package type:

| Package Type | Who is Shown | Rationale |
|--------------|--------------|-----------|
| **Only Validation Plans** | Individuals WITHOUT active validation | They need a base membership |
| **Validation + Non-Validation Plans** | All individuals (missing at least one plan) | They might need non-validation parts |
| **Only Non-Validation Plans (add-ons)** | Individuals WITH active validation | Add-ons require base membership |
| **Insurance-Only** | Individuals WITH active validation | Insurance requires base membership |

### Partial Plan Overlap (Critical Business Rule)

**Scenario**: A package contains Plan A (validation) + Plan B (territorial). An individual already has Plan A active.

**Behavior**:
1. **UI**: Individual IS shown (they're missing Plan B)
2. **Validation**: Subscription IS allowed (not all plans are duplicates)
3. **Creation**: Plan A affiliation is SKIPPED, only Plan B is created

This allows entities to subscribe individuals to mixed packages even if they already have some of the plans.

### Duplicate Prevention Rules

The `SubscriptionValidationService.checkDuplicateAffiliationPlans()` method:

| Scenario | Result |
|----------|--------|
| ALL plans are duplicates | **Error**: "You already have all affiliation plans in this package" |
| SOME plans are duplicates | **Allowed**: Subscription proceeds, duplicates are skipped |
| NO plans are duplicates | **Allowed**: All affiliations are created |

### Affiliation Creation Safety

The `CreateAffiliationAction` includes a safety check that skips creating affiliations when:
- Member already has an active affiliation for the same plan
- Affiliation is for the same federation
- Existing affiliation has not expired

This prevents duplicate affiliations even if validation somehow passes.

### Code Example

```php
// SubscriptionValidationService - Allow partial overlap
$duplicates = $newAffiliationPlanIds->intersect($existingAffiliationPlanIds);
$newPlans = $newAffiliationPlanIds->diff($existingAffiliationPlanIds);

// Only error if ALL plans are duplicates
if ($duplicates->isNotEmpty() && $newPlans->isEmpty()) {
    return ['valid' => false, 'error' => __('memberships.all_affiliation_plans_already_active')];
}

// Some duplicates but new plans exist - allow subscription
return ['valid' => true, 'error' => null];
```

```php
// CreateAffiliationAction - Skip existing affiliations
$existingAffiliation = Affiliation::where('member_type', $memberType)
    ->where('member_id', $memberId)
    ->where('federation_id', $affiliationPlan->federation_id)
    ->where('end_date', '>=', now())
    ->where('status_class', ActiveAffiliationState::class)
    ->exists();

if ($existingAffiliation) {
    Log::info('Skipping affiliation - member already has active affiliation for this plan');
    return null;
}
```

---

## 4. Insurance & Affiliation State Machine

Both `Insurance` and `Affiliation` models use the state pattern with `status_class` field, synchronized with the parent `MemberSubscription` status.

### State Classes

**Insurance States** (`Domain\Insurance\States\`):
- `PendingPaymentInsuranceState` - Default, awaiting payment
- `ActiveInsuranceState` - Paid and active
- `ExpiredInsuranceState` - Past expiration date
- `SuspendedInsuranceState` - Manually suspended

**Affiliation States** (`Domain\Memberships\States\`):
- `PendingPaymentAffiliationState` - Default, awaiting payment
- `ActiveAffiliationState` - Paid and active
- `ExpiredAffiliationState` - Past expiration date

### Status Synchronization

When a subscription status changes, child records are updated:

| MemberSubscription State | Insurance State | Affiliation State |
|-------------------------|-----------------|-------------------|
| `PendingPaymentMemberSubscriptionState` | `PendingPaymentInsuranceState` | `PendingPaymentAffiliationState` |
| `ActiveMemberSubscriptionState` | `ActiveInsuranceState` | `ActiveAffiliationState` |
| `ExpiredMemberSubscriptionState` | `ExpiredInsuranceState` | `ExpiredAffiliationState` |

### Key Behavior

- **Affiliations and Insurances are NOT active by default**
- They start with `PendingPayment` status when subscription requires payment
- They only become active when payment is confirmed
- This ensures coverage is only active after payment

### Usage

```php
// Check if insurance is active
if ($insurance->isActive()) {
    // Insurance is active and paid
}

// Get state name for display
$insurance->stateName(); // Returns localized state name

// Get state color for UI
$insurance->stateColor(); // Returns 'success', 'warning', 'danger', etc.
```
