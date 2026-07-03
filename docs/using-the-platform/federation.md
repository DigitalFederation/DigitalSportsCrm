---
title: Federation Portal
description: The federation officer's view — reviewing members, issuing licenses, running events, task by task
---

# Federation Portal

This is the operator guide for the **federation portal** (`/federation`) — for a federation
officer running a national or regional federation. It maps the everyday tasks to the screen that
performs them. Deep technical detail lives in the linked pages; this is the "where do I click"
map. For the other portals, see [Using the Platform](/using-the-platform/).

## Before you start

- You log in as a user in the **`FEDERATION`** group, tied to a `Federation` record. Almost every
  screen shows an error (or 403) if your account isn't linked to a federation.
- All screens live under **`/federation`**. You land on **`/federation/dashboard`**.
- **Your federation's _type_ changes what you see** — this is the single biggest source of "why
  can't I do X":

  | Federation type | What it can do |
  |---|---|
  | **Main / default** (the national governing body) | Sees **all** data; can create events, validate diving licenses, manage application templates, issue certifications |
  | **Local / territorial** | Sees only **its own members'** data; many management screens are read-only or submit-only |

  The same URL shows all-data for the main federation but members-only for a local one. Type is
  set on the federation record by an admin — it isn't switchable from this portal.

## Task → screen map

### Members

| I want to… | Go to | Notes |
|---|---|---|
| Browse individual members | `/federation/individuals` | Filterable roster of affiliated individuals |
| Add / view / edit an individual | `/federation/individuals/create` · `/federation/individual/{id}` · `/…/edit` | Creating also provisions their user account + member number |
| Approve or reject individual join requests | `/federation/individual-requests` | Pending affiliation queue |
| Browse club (entity) members | `/federation/entities` | Roster of affiliated clubs |
| Create a club | `/federation/entity/create` | Requires the `create entities` permission |
| Approve or reject club join requests | `/federation/entity-requests` | Pending affiliation queue |

### Memberships, affiliations & insurance

| I want to… | Go to | Notes |
|---|---|---|
| Review this federation's memberships | `/federation/memberships` | See [Memberships](/features/memberships) |
| Affiliate a club / an individual | `/federation/entity-affiliations/create` · `/federation/individual-affiliations/create` | Subscribe a member to a plan |
| Sell insurance-only cover | `/federation/entity-insurances/create` | Insurance packages for members |
| Define **local** membership plans | `/federation/local-membership-plan` | National plans are created admin-side |

### Licenses

| I want to… | Go to | Notes |
|---|---|---|
| Browse attributed licenses | `/federation/licenses-attributed` | Rich filters (committee, holder, status, expiry) |
| Issue a license | `/federation/license-attributed/create/{type}/{committee}` | Scoped to a license type + committee |
| See a committee's licenses | `/federation/{slug}-{holder}-licenses-attributed` | Per-committee tabs generated from `config/committees.php` |
| **Validate** submitted diving licenses | `/federation/entity-diving-license-validation` · `/federation/individual-diving-license-validation` | **Main federation only** |

### Certifications

Requires your federation's **"can issue certifications"** flag (set admin-side). Without it this
whole area is hidden except for viewing a single certification.

| I want to… | Go to | Notes |
|---|---|---|
| Browse attributed certifications | `/federation/certifications-attributed` | See [Certifications](/features/certifications) |
| Issue a certification | `/federation/certification-attributed/create` · `/…/wizard/create` | Form or step-by-step wizard |
| View a certification | `/federation/certification-attributed/{id}` | The one cert screen open to **all** federations |

### Events & enrollments

| I want to… | Go to | Notes |
|---|---|---|
| Browse events | `/federation/evt-events/events` | Main fed sees all; others limited |
| Create / edit an event | `/federation/evt-events/events/create` · `/…/{event}/edit` | Create is **main federation only** (+ `manage-events`) |
| Review enrollments (athlete/coach/official/staff/individual) | `/federation/evt-events/events/{event}/athlete-enrollment` (and the `coach-`, `officials-`, `staff-`, `individual-enrollment` siblings) | See [Event Enrollment Roles](/features/event-enrollment-roles) |
| Referee enrollments | `/federation/evt-events/events/{event}/referee-enrollment` | **Main federation only** |
| Review & pay an enrollment cart | `/federation/evt-events/events/{event}/review` | See [Payments](/features/payments) |

### Event applications (candidatures)

| I want to… | Go to | Notes |
|---|---|---|
| See applications | `/federation/event-applications` | Main fed = review queue; local fed = its territorial view |
| Apply to host an event | `/federation/event-applications/available-templates` · `/…/create/direct` | From a template or free-form |
| Manage application templates | `/federation/application-templates` | **Main federation only** in practice |

### Documents, billing & diagnostics

| I want to… | Go to | Notes |
|---|---|---|
| Review members' official documents | `/federation/official-documents` | Needs `access federation official documents` |
| Manage the federation's own documents | `/federation/my-official-documents` | — |
| View / pay invoices | `/federation/documents` · `/federation/document/{id}` | See [Payments](/features/payments) |
| Check member eligibility | `/federation/diagnostics` | Eligibility Diagnostic Center |

## Gates & gotchas

- **Main-federation-only** areas: event creation, referee enrollment, diving-license validation,
  and application-template management. Local federations get read-only or submit-only equivalents.
- **Certifications** require the per-federation `can_issue_certifications` flag (admin-set).
- **Permissions** gate specific actions: `create entities`, `manage-events`, `access federation
  official documents`. How these are granted is in [Access Control](/access-control/role-management).
- **Empty screens are often correct**: a local federation with no members, or a federation not
  linked to a committee, will see empty rosters and empty committee tabs — that's configuration,
  not a fault.

## Managed elsewhere (not a screen here)

- **Committees** and their per-committee license/certification tabs — `config/committees.php`. See
  [Configuring Committees](/guides/configuring-committees).
- **Federation type** (`main` / `local`) and the **can-issue-certifications** flag — set on the
  federation record by an admin.
- **National membership, affiliation, and insurance plans** — created in the admin portal; the
  federation only subscribes members to them (it can define *local* plans).

## See also

- [Using the Platform](/using-the-platform/) — the other portals
- [Memberships](/features/memberships) · [Licenses](/features/licenses) · [Certifications](/features/certifications)
- [Events](/features/events) · [Event Enrollment Roles](/features/event-enrollment-roles) · [Event Applications](/features/event-applications)
- [Federation Membership Rules](/access-control/federation-membership-rules) · [Federation License Permissions](/access-control/federation-license-permissions)
