---
layout: home
title: Home
hero:
  name: Digital Sports CRM
  text: Platform Documentation
  tagline: Technical and functional documentation for a federation management platform (the Sports Federation deployment shown as the example)
  actions:
    - theme: brand
      text: Get Started
      link: /guides/getting-started
    - theme: alt
      text: View Features
      link: /features/memberships
features:
  - icon:
      src: /architecture-icon.svg
      alt: Architecture
    title: Architecture
    details: Technical deep-dives into DDD structure, committee system, and design patterns
    link: /architecture/01-overview
  - icon:
      src: /features-icon.svg
      alt: Features
    title: Features
    details: Memberships, licenses, certifications, events, payments, and more
    link: /features/memberships
  - icon:
      src: /access-icon.svg
      alt: Access Control
    title: Access Control
    details: Roles, permissions, federation membership rules
    link: /access-control/federation-membership-rules
  - icon:
      src: /guides-icon.svg
      alt: Guides
    title: Developer Guides
    details: Code style, UI patterns, and migration guides
    link: /guides/development-style-guide
---

## Quick Reference

### Key Business Rules

1. **Committee Structure** — config-driven; see [Configuring Committees](/guides/configuring-committees) and [Committee Structure](/architecture/02-committee-structure)
   - Committees and their national/international scope are defined in `config/committees.php`, not hardcoded.
   - In the example diving deployment: `SPORT`, `DIVINGSERVICES` are national (`is_international = false`); `DIVING`, `SCIENTIFIC` are international (`is_international = true`).

2. **Federation Membership** - See [Federation Membership Rules](/access-control/federation-membership-rules)
   - Entity join → Only LOCAL federations synced
   - License activation → sport/discipline federation synced

3. **License TD Approval** (example deployment rule) - See [Licenses](/features/licenses)
   - Entity + Diving license → TD approval required first
   - Individual + Diving license → Direct to federation validation

### For New Developers

1. Start with [Architecture Overview](/architecture/01-overview)
2. Read [Committee Structure](/architecture/02-committee-structure)
3. Review [Development Style Guide](/guides/development-style-guide)

### For Feature Work

- Check the relevant document in [Features](/features/memberships)
- Cross-reference with [Access Control](/access-control/federation-membership-rules) for permission requirements

---

## Documentation Structure

### Architecture
Technical deep-dives for developers understanding the system design.

| Document | Description |
|----------|-------------|
| [Overview](/architecture/01-overview) | Core principles, DDD structure, model relationships, design patterns |
| [Committee Structure](/architecture/02-committee-structure) | Committee types, internationality flags, federation access control |
| [Diving Entity Relationships](/architecture/03-diving-entity-relationships) | Entity-individual relationships in diving context |
| [Diving Professionals Architecture](/architecture/04-diving-professionals-architecture) | Diving professionals vs Technical Directors architecture |

### Features
Feature documentation for developers and administrators.

| Document | Description |
|----------|-------------|
| [Memberships](/features/memberships) | Affiliation plans, insurance, subscription validation, state machines |
| [Licenses](/features/licenses) | License purchase, validation flow, TD approval, state machine |
| [Certifications](/features/certifications) | Certification attribution wizard, pricing, requirements |
| [Events](/features/events) | Event registration, enrollment rules, credits, applications |
| [Event Reports](/features/event-reports) | Technical Delegate and Chief Judge post-event reports |
| [Diving Professionals](/features/diving-professionals) | Diving professional licensing module |
| [Payments](/features/payments) | Payment gateway architecture, EasyPay integration |
| [Event Applications](/features/event-applications) | Event candidature system technical specs |
| [Import System](/features/import-system) | Bulk individual import from CSV/XLS |
| [Platform Utilities](/features/platform-utilities) | Badges, zones, operations center |

### Access Control
Roles, permissions, and membership rules for administrators.

| Document | Description |
|----------|-------------|
| [Federation Membership Rules](/access-control/federation-membership-rules) | **CRITICAL** - Local vs sport/discipline federation rules |
| [Role Management](/access-control/role-management) | Dynamic role management system |
| [Permission Management](/access-control/permission-management) | Dynamic permission system |
| [Individual Roles](/access-control/individual-roles) | Individual namespace roles and triggers |
| [Entity Roles](/access-control/entity-roles) | Entity namespace roles and triggers |
| [Federation License Permissions](/access-control/federation-license-permissions) | Federation-specific license permissions |

### Guides
How-to guides and style references for developers.

| Document | Description |
|----------|-------------|
| [Development Style Guide](/guides/development-style-guide) | Code style, UI patterns, component usage for LLMs |
| [Frontend Style Guide](/guides/frontend-style-guide) | Tailwind CSS patterns for public pages |
| [Creating a Plugin](/guides/creating-a-plugin) | Build domain-specific verticals as auto-discovered Composer packages |

---

## Maintenance

This documentation should be updated when:
- New features are added
- Business rules change
- Architecture decisions are made

Keep documentation DRY - reference other docs instead of duplicating content.
