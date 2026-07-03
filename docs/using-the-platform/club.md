---
title: Club Portal
description: The club manager's view — members, licenses, enrolling athletes into events, task by task
---

# Club Portal

This is the operator guide for the **club portal** (`/entity`) — for a club / entity manager
(a sports club, school, dive centre, or company). It maps the everyday tasks to the screen that
performs them. Deep technical detail lives in the linked pages; this is the "where do I click"
map. For the other portals, see [Using the Platform](/using-the-platform/).

> "Entity" is the internal name for a club/organization. In the URLs and role names you'll see
> `entity`; in the interface it's your club.

## Before you start

- You log in as a user in the **`ENTITY`** group, attached to one club. Most screens show an error
  (or 403) if your account isn't linked to a club.
- All screens live under **`/entity`**. You land on **`/entity/dashboard`**, which surfaces your
  club's affiliations, licenses, and a **"pending members to approve"** queue.
- **Your role within the club decides what you can do.** Roles (`entity-admin`,
  `entity-sport`, `entity-diving-services`, `entity-international`) are assigned **by an admin**,
  not self-granted. `entity-admin` can do everything; the others unlock specific areas.
- **Some areas need an active club license.** You can only invite instructors for a committee if
  your club holds an active license for it — otherwise the screen redirects with an error. That's
  a licensing prerequisite, not a bug.

## Task → screen map

### Club setup

| I want to… | Go to | Notes |
|---|---|---|
| Edit the club profile (name, VAT, logo, description) | `/entity/profile/edit` | Needs role `entity-admin` or `entity-diving-services` |
| Manage the public club page | `/entity/public-page` | Same roles |
| See the federations we belong to | `/entity/federations` | See [Federation Membership Rules](/access-control/federation-membership-rules) |
| Manage committee attachments | `/entity/attachments` | Files shared per committee |

### Members

| I want to… | Go to | Notes |
|---|---|---|
| List club members | `/entity/individuals` | Active members of this club |
| Add / view / edit a member | `/entity/individuals/create` · `/entity/individual/{id}` · `/…/edit` | Creates the member (and optional user account) |
| Approve pending join requests | `/entity/individual-approve` | Approving assigns a member number and syncs roles |

### Memberships & insurance

| I want to… | Go to | Notes |
|---|---|---|
| Manage the club's own subscriptions | `/entity/subscriptions` | Subscribe / renew the club |
| Buy insurance-only cover | `/entity/insurances` | Club insurance packages |
| Bulk-subscribe members to a plan | `/entity/individual-memberships` · `/…/preview/{package}` | Needs the `manage-individual-subscriptions` ability; preview shows eligible members first |
| Assign insurance to members | `/entity/individual-insurances` | — |

### Licenses

| I want to… | Go to | Notes |
|---|---|---|
| See the club's attributed licenses | `/entity/licenses-attributed` · `/…/individuals` | All, or broken down per member |
| Buy licenses (per committee) | `/entity/{slug}-license-purchase` (+ `-member-` variants) | Pages generated from `config/committees.php`; `/entity/license-purchase` redirects to the first configured one |
| See a committee's licenses | `/entity/{slug}-licenses-attributed` | Per-committee, entity- or member-holder |

### Certifications

Reached through committee-scoped buttons; a bare create URL needs a committee filter.

| I want to… | Go to | Notes |
|---|---|---|
| Browse attributed certifications | `/entity/certifications` · `/entity/certifications-attributed` | See [Certifications](/features/certifications) |
| Attribute a certification | `/entity/certification-attributed/wizard/create` | Guided wizard (the plain `/create` needs a `filter.committee`) |

### Diving operations

| I want to… | Go to | Notes |
|---|---|---|
| Manage diving professionals | `/entity/diving-professionals` | Active + pending invitations |
| Manage instructors (diving / scientific / international) | `/entity/diving-instructors` · `/entity/scientific-instructors` · `/entity/international-diving-instructors` | **Each needs an active license** for its committee |
| See the club's diving licenses | `/entity/diving-licenses` | Includes assigned technical directors |
| Request a diving license | `/entity/diving-licenses/request` | Livewire wizard (`/create` redirects here) |
| **Invite a technical director** | `/entity/diving-licenses/{license}/invite-director` | Also `/…/directors` to manage them |

### Events & enrollments

Your club sees an event only if the organizer configured it to include clubs.

| I want to… | Go to | Notes |
|---|---|---|
| Browse competitions / organization events | `/entity/evt-events/competitions` · `/entity/evt-events/organization` | `/entity/evt-events` redirects to competitions |
| View an event | `/entity/evt-events/events/{event}` | Detail: disciplines, referees, your enrollment status |
| Enroll athletes / coaches / officials / staff / individuals | `/entity/evt-events/events/{event}/athlete-enrollment` (and the `coach-`, `officials-`, `staff-`, `individual-enrollment` siblings) | See [Event Enrollment Roles](/features/event-enrollment-roles) |
| Review & pay | `/entity/evt-events/events/{event}/review` | Handles pricing/dedup — see [Payments](/features/payments) |
| See confirmed enrollments | `/entity/evt-events/events/{event}/confirmed-enrollments` | After payment |

### Event applications (candidatures)

| I want to… | Go to | Notes |
|---|---|---|
| Apply to host / run an event | `/entity/event-applications/available-templates` · `/…/create/direct` | From a template or free-form |

### Documents & payments

| I want to… | Go to | Notes |
|---|---|---|
| List / pay invoices | `/entity/documents` · `/entity/document/{id}` | See [Payments](/features/payments) |
| See official documents issued to the club | `/entity/official-documents` | — |

## Gates & gotchas

- **No club linked → 403** on most screens (the dashboard shows a banner instead).
- **Instructor screens** require an active club license for that committee
  (`diving` / `scientific` / `sport`). Without it you're redirected with a "needs active license"
  message.
- **Certification screens** are role-mapped: a committee filter maps to an `entity-*` role, and
  the screen 403s if you lack it. `entity-admin` bypasses this.
- **Profile / public page** need `entity-admin` or `entity-diving-services`.
- **International** purchases and certifications need the `entity-international` role.
- **Empty committee tabs are configuration, not bugs** — they appear only if your club is wired to
  that committee.

## Managed elsewhere (not a screen here)

- **License purchase / attributed pages per committee** — generated from `config/committees.php`;
  which pages you see is driven entirely by committee wiring. See
  [Configuring Committees](/guides/configuring-committees).
- **Your club's roles** (`entity-admin`, etc.) — assigned by an admin; you can't self-grant them.
- **Event visibility & enrollment eligibility** — set by the event's organizer, not a club screen.
- **Which membership packages you can buy** — admin-configured; the club only subscribes.

## See also

- [Using the Platform](/using-the-platform/) — the other portals
- [Memberships](/features/memberships) · [Licenses](/features/licenses) · [Certifications](/features/certifications) · [Diving Professionals](/features/diving-professionals)
- [Events](/features/events) · [Event Enrollment Roles](/features/event-enrollment-roles) · [Event Applications](/features/event-applications)
- [Entity Roles](/access-control/entity-roles) — the club roles referenced above
