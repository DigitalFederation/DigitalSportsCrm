---
title: Using the Platform
description: The four portals, who logs into each, and where to find the operator guide for your role
---

# Using the Platform

The application is not one interface — it is **four portals**, one per user group. When you log
in, you land in the portal for **your account's group**; you never switch between them in a
session. Each portal is a different persona with its own sidebar, its own URLs, and its own set
of tasks.

If a screen someone describes "isn't there" for you, it is almost always because it lives in a
**different portal** (a different login) or is gated by a role/feature flag — not because it is
missing. Start from the guide for your portal below.

## The four portals

| Portal | Who logs in | URL prefix | Guide |
|---|---|---|---|
| **Admin** | Platform operators / administrators | `/admin` | [Admin Portal](/using-the-platform/admin) |
| **Federation** | Federation officers (national or local) | `/federation` | [Federation Portal](/using-the-platform/federation) |
| **Club** | Club / entity managers | `/entity` | [Club Portal](/using-the-platform/club) |
| **Individual** | Members — athletes, coaches, officials, instructors | `/individual` | [Individual Portal](/using-the-platform/individual) |

Which portal your account uses is determined by its **user group**, set when the account is
created. A person can only have one active portal per account.

## What every portal shares

A few things work the same way across all four — and they trip up new operators the same way:

- **The sidebar is the database menu.** If a link you expect is missing, that is usually seeding
  or a feature flag, not a missing feature. See [Navigation & Menus](/guides/navigation-and-menus).
- **What you can see and do is governed by roles and permissions.** Two people in the same portal
  can see different screens. See [Access Control](/access-control/role-management).
- **Committees are configured, not clicked.** Which committees exist — and therefore which
  license, certification, and menu sections appear in every portal — comes from
  `config/committees.php`, not an in-app screen. See
  [Configuring Committees](/guides/configuring-committees). An empty committee tab is
  configuration, not a bug.
- **Structure is config-and-seed; operations are in-app.** Definitions (committees, the canonical
  menu tree, plans) are set in config/admin so a deployment is reproducible; day-to-day work
  (approving members, enrolling athletes, issuing licenses) happens in the portals.

## See also

- [Getting Started](/guides/getting-started) — install and first admin account
- [Navigation & Menus](/guides/navigation-and-menus) — the sidebar and its seeding
- [Configuring Committees](/guides/configuring-committees) — the committee model that shapes every portal
- [Access Control](/access-control/role-management) — roles, permissions, and membership rules
