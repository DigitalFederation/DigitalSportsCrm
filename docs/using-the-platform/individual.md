---
title: Individual Portal
description: The member's self-service view — licenses, certifications, enrollments, documents, task by task
---

# Individual Portal

This is the guide for the **individual portal** (`/individual`) — the self-service area for a
member acting for themselves: an athlete, coach, referee/judge, instructor, diving professional,
or technical delegate. It maps what you can do to the screen that does it. Deeper detail lives in
the linked pages. For the other portals, see [Using the Platform](/using-the-platform/).

## Before you start

- You log in as a user in the **`INDIVIDUAL`** group with an **approved individual profile**.
  Without an approved profile most screens return 403 — approval is done by an admin or federation.
- All screens live under **`/individual`**. You land on **`/individual/dashboard`**, a hub that
  preloads your federations, clubs, certifications, licenses, subscriptions, and insurance.
- **Your menu depends on your qualifications.** Screens only appear if you hold the matching role
  (e.g. the coach area needs the coach role, the judge area needs the technical-official role).
  These roles are **assigned when a club or federation gives you that qualification** — you can't
  self-assign them; you accept the invitation and the role follows.

## Task → screen map

### My profile

| I want to… | Go to |
|---|---|
| See my hub / overview | `/individual/dashboard` |
| View / edit my profile | `/individual/profile` · `/individual/profile/edit` |

### My licenses

| I want to… | Go to | Notes |
|---|---|---|
| See my licenses | `/individual/licenses-attributed` | See [Licenses](/features/licenses) |
| Buy a committee license | `/individual/{committee-slug}` | One page per committee, from `config/committees.php` |
| See a committee's licenses | `/individual/{committee-slug}-licenses-attributed` | Per-committee list |

### My certifications

| I want to… | Go to | Notes |
|---|---|---|
| See my certifications | `/individual/certifications` | See [Certifications](/features/certifications) |
| View one certification | `/individual/certification-attributed/{id}` | Activate / cancel actions live here |
| Get my certification card | `/individual/certification-card` · `/individual/international-certification-card` | National and international |
| Validate certifications | `/individual/certification-validate` | Activate / reject certs assigned to you |

### Clubs, federations & invitations

| I want to… | Go to | Notes |
|---|---|---|
| See my federations / clubs | `/individual/federation` · `/individual/entity` | Join / leave actions on the entity screen |
| Accept a coach / athlete invitation | `/individual/coach` · `/individual/athlete` | Invites from clubs; accept or decline |
| Accept an instructor invitation | `/individual/instructor/{committee}` | Per committee |
| Request to join a federation | `/individual/federation-request/create` | Submit an association request |

### Memberships, insurance & payments

| I want to… | Go to | Notes |
|---|---|---|
| See / subscribe to memberships | `/individual/subscriptions` · `/individual/subscriptions/create` | See [Memberships](/features/memberships) |
| See my insurance | `/individual/insurance` | Coverage + documents |
| View / pay my invoices | `/individual/documents` · `/individual/document/{id}` | See [Payments](/features/payments) |

### Events & official roles

| I want to… | Go to | Notes |
|---|---|---|
| Browse competitions / events | `/individual/evt-events/competitions` · `/individual/evt-events/organization` | — |
| View an event and enroll | `/individual/evt-events/events/{event}` | Enrollment starts here |
| Join a full event's waiting list | `/individual/evt-events/events/{event}/waiting-list` | — |
| See my coach / referee history | `/individual/event-coach-history` · `/individual/referee-history` | Past events and functions |
| See all my official roles | `/individual/event-official-roles` | Every event where you hold a role |
| Write a Technical Delegate / Chief Judge report | `/individual/technical-delegate/{event}/td-report` · `/…/cj-report` | Only for the event you're assigned to |

### Diving professional module

| I want to… | Go to | Notes |
|---|---|---|
| Manage my diving certifications | `/individual/diving-certifications` | See [Diving Professionals](/features/diving-professionals) |
| Manage my diving centres | `/individual/diving-entities` | Centres I'm linked to |
| Manage professional relationships | `/individual/diving-professionals` | Accept / reject / end |
| Technical-director positions | `/individual/technical-director-positions` | License-based TD approvals |

### Documents

| I want to… | Go to | Notes |
|---|---|---|
| See my official documents (per role) | `/individual/official-documents/{role}` | Role must be one you hold |
| Manage my attachments | `/individual/attachments/attachments` | Personal uploads |

## Gates & gotchas

- **You need an approved individual profile** — without it, most screens 403.
- **The menu is role-gated** — you only see areas for roles you hold; those roles come from
  qualifications a club or federation assigns.
- **Committee license pages are dynamic** — the `/individual/{slug}` pages exist only for
  committees in `config/committees.php`; slugs vary per deployment.
- **Report screens** (TD / chief judge) need you to be assigned that role **on that specific
  event**, not just to hold the role generally.
- **You can only see your own** documents and invoices (policy-enforced).

## Managed elsewhere (not self-serve)

- **Getting a qualification / role** — assigned by a club or federation; you accept the invitation.
- **Approving your profile** — an admin/federation action.
- **Attributing your licenses & certifications** — issued by a federation/committee; you view,
  activate, or validate what's assigned.
- **Membership package contents, insurance plans, event windows & pricing** — configured by
  federations / organizers; you subscribe or enroll.

## See also

- [Using the Platform](/using-the-platform/) — the other portals
- [Licenses](/features/licenses) · [Certifications](/features/certifications) · [Memberships](/features/memberships)
- [Events](/features/events) · [Event Enrollment Roles](/features/event-enrollment-roles) · [Event Reports](/features/event-reports) · [Diving Professionals](/features/diving-professionals)
- [Individual Roles](/access-control/individual-roles) — the roles that unlock each area
