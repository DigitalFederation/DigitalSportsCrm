---
title: Diving Entity Relationships
description: Relationship structure between individuals, entities, and diving roles
---

# Diving Entity Relationships

## Overview

This document explains the relationships between Individuals, Entities, and Diving-related roles in the system.

> The diving roles described here belong to the example (reference) deployment. The underlying model — Individual↔Entity associations, professional roles, and entity athletes — is generic to the platform; diving is one configured set of roles built on top of it.

---

## Core Relationships

### Individual ↔ Entity

An individual can be associated with multiple entities, and entities can have multiple individuals. This relationship is created when:

- An individual joins an entity as a member
- An individual is invited as an athlete
- An individual accepts an invitation as a diving professional

### Entity ↔ Professional Role

Entities can have professional relationships with individuals (instructors, diving professionals). These are managed separately from general membership.

---

## Diving Professionals

**Role:** `DIVINGPROFESSIONAL`
**Committee:** `DIVING`

### Flow

1. Entity invites individual as diving professional
2. Invitation created with "Pending" status
3. Individual accepts → Status becomes "Active"
4. Individual automatically becomes entity member

---

## Entity Athletes

Entities can register individuals as athletes for specific sports.

### Key Rules

- Athletes are associated with a specific sport
- Entities can invite individuals as athletes
- Entities can disassociate athletes from their club
- Soft delete is used (records are preserved)

### Flow

1. Entity invites individual as athlete for a sport
2. Individual appears in entity's athlete list
3. Entity can disassociate athlete when needed

---

## State Summary

### Professional Role States

| State | Meaning |
|-------|---------|
| Pending | Invitation sent, awaiting response |
| Active | Active professional relationship |
| Rejected | Invitation rejected or relationship ended |
| Canceled | Invitation canceled by entity |

### Individual-Entity States

| State | Meaning |
|-------|---------|
| Active | Active membership |
| Pending | Awaiting approval |
| Pending from Entity | Entity invited individual |
| Pending from Individual | Individual requested to join |

---

## Important Notes

1. **Automatic Membership:** When a professional role is accepted, individual automatically becomes entity member
2. **Separate Concepts:** Diving professionals and Technical Directors are different (see [Diving Professionals Architecture](./04-diving-professionals-architecture))
3. **Soft Deletes:** Athlete relationships use soft delete for audit trail
