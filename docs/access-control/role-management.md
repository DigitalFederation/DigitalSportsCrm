---
title: Role Management
description: Dynamic role management system for administrators
---

# Role Management System

## Overview

The Dynamic Role Management System enables administrators to create, modify, and manage user roles and permissions. It includes built-in safeguards to prevent system lockouts and unauthorized access.

---

## Role Hierarchy

> The platform is a generic federation-management system. The role names below are the
> actual values seeded by `RoleAndPermissionSeeder` (after the role-system simplification).
> The diving-specific naming in some roles reflects the example diving deployment; the codes
> themselves are stable identifiers.

### Platform Administration (top tier)
- **admin** - Full platform access. In the diving example deployment this is the root-organization tier.

### Associations
- **association-sport-admin** - Sport association administration
- **association-admin** - Top-tier association administration (diving and scientific scope)
- **association-scientific-admin** - Scientific association administration
- **association-territorial-admin** - Territorial (local) association administration

### Federations
- **federation-admin** - Main federation management

### Entities (Clubs/Schools/Centers)
- **entity-admin** - Entity management
- **entity-sport** - Sport-focused entity access
- **entity-diving-services** - Diving-services entity access
- **entity-international** - Entity authorized for international content

### Individuals
- **individual-approved** - Basic approved individual
- **individual-coach** - Coach role
- **individual-instructor** - Instructor role
- **individual-athlete** - Athlete role
- Various specialized roles (technical-official, leader, diver, scientific, sport, view-* roles, etc.)

---

## Core Features

### Role Management
- Create, edit, and delete roles (`RoleManagementController`)
- Role templates for common role types (`RoleManagementService::getActiveTemplates()`)
- Role duplication for quick setup (the `duplicate` action)
- Searchable role list with usage statistics (`searchRoles`, `getStatistics`)

### Permission Management
- Custom permission creation, grouped by functional area (the `category` field)
- Route-permission mapping (`RoutePermissionTable` / `RoutePermissionService`)
- Per-role permission assignment (`syncPermissions`)

### Security Features

**Protected Roles:**
- System roles cannot be deleted (`is_protected` / `protection_level`)
- Critical admin roles are protected
- Minimum super-admin count enforced — the last `admin` cannot be deleted (`SecurityValidationService::MINIMUM_SUPER_ADMIN_COUNT`)
- Roles still assigned to users cannot be deleted

**Audit Trail (roles):**
- Role changes are logged with user attribution via Spatie Activitylog (the `Role` model's `LogsActivity`, logging `name`, `description`, `category`, `is_protected`, `protection_level`)
- Old and new values are captured for each logged change

> Note: permission records themselves are not activity-logged — only role changes are.

---

## Permission Categories

> Permission names below are real values from `RoleAndPermissionSeeder`. Use the seeder as
> the source of truth for the full list.

| Category | Examples |
|----------|----------|
| User Management | access users, manage user roles, impersonate users |
| Federation Management | access federations, access memberships |
| Entity Management | access entities, create entities |
| License Management | access licenses, access licenses manager |
| Certification Management | access certifications, access certification slots manager |
| Document Management | access documents, access official documents, download reports |
| System Administration | access settings, manage_menus |

---

## Security Rules

1. **Hierarchical Access**: Lower-level admins cannot modify higher-level roles
2. **Scope Limitations**: Federation admins can only modify federation-level roles
3. **Protected Roles**: System-critical roles cannot be deleted
4. **Minimum Admins**: System ensures at least one super-admin always exists
5. **Confirmation Required**: Dangerous operations require multi-step confirmation
