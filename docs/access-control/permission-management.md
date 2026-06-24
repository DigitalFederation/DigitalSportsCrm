---
title: Permission Management
description: Dynamic permission management system for administrators
---

# Permission Management System

## Overview

The Dynamic Permission Management System allows administrators to create, manage, and assign permissions through the UI without modifying code.

---

## Key Features

### Permission Management
- Create, edit, and delete permissions
- Organize permissions by categories
- Bulk operations and import/export
- Protected system permissions that cannot be deleted

### Route-Permission Mapping
- Map permissions to routes dynamically
- Route scanner to identify unprotected routes
- Coverage statistics for route protection

---

## Permission Categories

| Category | Description |
|----------|-------------|
| Users | User account management |
| Roles | Role management |
| Federations | Federation management |
| Entities | Entity/club management |
| Licenses | License management |
| Certifications | Certification management |
| Events | Event management |
| Documents | Document management |
| Settings | System settings |

---

## Route Protection

### How It Works
1. Admin maps permission to route
2. Middleware checks user has permission
3. Access granted or denied

### Coverage Statistics
- Shows percentage of protected routes
- Identifies unprotected routes
- Suggests permissions based on route patterns

---

## Business Rules

### System Permissions
Certain core permissions are protected from deletion (`DeletePermissionAction`):
- Cannot be deleted (e.g., `access users`, `manage user roles`, `manage permissions`, `manage protected roles`)
- A permission that is still assigned to one or more roles cannot be deleted (unassign it first)

### Permission Naming
- Lowercase, space-separated
- Pattern: `action resource` (e.g., `access users`, `access licenses`). A few legacy permissions use other separators (e.g., `manage_menus`, `manage-events`).

### Deletion Safety
Permissions are guarded against unsafe removal (see System Permissions above).

> Note: unlike roles, permission records are not activity-logged. Role changes are audited via the `Role` model — see [Role Management](/access-control/role-management).

---

## Related Documentation

- [Role Management](/access-control/role-management)
- [Individual Roles](/access-control/individual-roles)
- [Federation License Permissions](/access-control/federation-license-permissions)
