---
title: Admin Portal
description: The admin panel from an operator's point of view — which screen does what, and where to find it
---

# Admin Portal

This is the operator guide for the **admin portal** (`/admin`) — for administrators running the
platform, not for developers editing code. It maps the everyday admin tasks to the screen that
performs them. The deep technical detail lives in the linked pages; this is the "where do I
click" map. For the other portals, see [Using the Platform](/using-the-platform/).

If you came here because something "isn't there," read [Managed in the UI vs. in
config](#managed-in-the-ui-vs-in-config) first — a few things (committees, the shape of the
sidebar) are configured in files and seeded, not edited on a screen. That distinction trips up
most new operators.

## Before you start

- You need a user with the **`admin` role** (full platform access). If you don't have one yet,
  see [Getting Started → default admin](/guides/getting-started).
- All admin screens live under **`/admin`** and require the `ADMIN` user group.
- The sidebar you see is the **database menu**. If a link you expect is missing, that is almost
  always a seeding step or a feature flag — not a missing feature. See
  [Navigation & Menus](/guides/navigation-and-menus).

## Task → screen map

| I want to… | Go to | Notes |
|---|---|---|
| See platform stats | `/admin/dashboard` | Landing screen after login |
| Create or edit a user account, resend the invite email | `/admin/users` | Users, their roles, and invitations |
| Merge two duplicate user accounts | `/admin/users/merge` | Consolidates one user into another |
| Create/edit **roles** and assign permissions to them | `/admin/role-management` | [Role Management](/access-control/role-management) |
| Create/edit individual **permissions** | `/admin/permission-management` | [Permission Management](/access-control/permission-management) |
| Require a permission to reach a route | `/admin/route-permissions` | Maps routes → permissions |
| Map licenses/certifications/federations to roles | `/admin/role-mappings` | Auto-role assignment rules |
| Reorder, rename, show/hide, or add **sidebar entries** | `/admin/menu-management` | Gated by flags — see [Navigation & Menus](/guides/navigation-and-menus) |
| Add or change a **committee** | **Config, not a screen** → `config/committees.php` | [Configuring Committees](/guides/configuring-committees) |
| Manage affiliation (membership) plans | Admin sidebar → Memberships | [Memberships](/features/memberships) |
| Manage zones & districts (reference data) | Admin sidebar → Settings | [Platform Utilities](/features/platform-utilities#_3-zones-districts-module) |
| Run maintenance commands / inspect queues | `/admin/operations` | [Operations Center](/features/platform-utilities#_4-operations-center) |
| Take or restore database backups | `/admin/backups` | Operator-only |
| Diagnostics & error reports | `/admin/diagnostics` | Operator-only |

## The screens you'll use most

### Users & accounts — `/admin/users`

Create and edit user accounts, set which **roles** a user holds (roles carry the permissions),
and resend the account-invitation email. Roles themselves are defined on the Role Management
screen — here you only assign existing roles to a person. Use `/admin/users/merge` to fold a
duplicate account into the correct one.

### Roles & permissions — `/admin/role-management`, `/admin/permission-management`

Permissions are the atomic "can do X" flags; roles are named bundles of permissions; users hold
roles. So the usual flow is: define the permission (if it's new) → add it to a role → assign the
role to the user. Both screens are dynamic — you don't edit code to add a role or permission.
Full rules and the built-in role hierarchy are in
[Role Management](/access-control/role-management) and
[Permission Management](/access-control/permission-management).

### Menus — `/admin/menu-management`

Reorder, rename, show/hide, and add sidebar entries per portal, without editing config. It only
loads when the `MenuSeeder` has run, the `DYNAMIC_MENU_ADMIN` flag is on, and your role has
`manage_menus` — see the prerequisite table in
[Navigation & Menus](/guides/navigation-and-menus#managing-menus-in-the-admin-ui). In-app edits
are per-deployment and are overwritten on the next re-seed.

## Managed in the UI vs. in config

Not everything is a screen — and knowing which is which saves a lot of hunting:

| Concern | Where you manage it |
|---|---|
| Users, roles, permissions, route protection | **Admin UI** (screens above) |
| Sidebar entries (order, labels, visibility) | **Admin UI** — `/admin/menu-management` (after seeding) |
| **Committees** (which exist, their scope, their menu/license/purchase wiring) | **Config** — `config/committees.php`, then re-seed. There is deliberately no committee-CRUD screen so the platform stays generic. See [Configuring Committees](/guides/configuring-committees). |
| Which menu the app renders, and per-portal toggles | **Config/env** — `DYNAMIC_MENU_*` flags. See [Navigation & Menus](/guides/navigation-and-menus). |

The rule of thumb: **structure and definitions** (committees, the canonical menu tree) are
config-and-seed so a fresh deployment is reproducible; **day-to-day operations and
deployment-specific tweaks** (users, roles, menu order) are done in the admin UI.

## See also

- [Using the Platform](/using-the-platform/) — the other portals (Federation, Club, Individual)
- [Getting Started](/guides/getting-started) — install, first admin account
- [Navigation & Menus](/guides/navigation-and-menus) — the sidebar, seeding, and the menu editor
- [Configuring Committees](/guides/configuring-committees) — committee sections via config
- [Access Control](/access-control/role-management) — roles, permissions, membership rules
- [Platform Utilities](/features/platform-utilities) — zones/districts, operations center
