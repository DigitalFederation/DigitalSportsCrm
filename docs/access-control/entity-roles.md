# Entity Namespace: Roles and Permissions

This document outlines the roles available for the `Entity` namespace, their permissions, and how they are triggered within the system. This information is derived from the `RoleAndPermissionSeeder` and its usage throughout the application.

> This platform is a generic federation-management system. The diving-specific naming in some
> role identifiers reflects the example diving deployment; the codes themselves are the
> actual, stable values seeded by `RoleAndPermissionSeeder`.

## Summary of Entity Roles

The `Entity` namespace has four roles that grant specific permissions to entities (e.g., clubs, schools, companies). In the current (simplified) role system the entity roles are:

### Core Entity Roles

-   `entity-admin`: The primary administrative role for an entity. Grants `access memberships`, `access individuals`, `access events`, `access orders`, and `access files area menu`.
-   `entity-sport`: For sport-focused entities. Grants `access memberships`, `access individuals`, `access events`, `access sport attachments`, `access sport menu`, and `access files area menu`.
-   `entity-diving-services`: For diving-services entities. Grants `access memberships`, `access individuals`, `access events`, `access diving attachments`, `access diving menu`, and `access files area menu`.
-   `entity-international`: For entities authorized to handle international certifications. Grants `access orders`. (Formerly `entity-company`.)

> **Note:** The pre-migration entity roles `entity-sport-admin`, `entity-diving-admin`, `entity-scientific-admin`, and `entity-company` no longer exist. The migration renamed `entity-sport-admin` → `entity-sport`, `entity-diving-admin` → `entity-diving-services`, and `entity-company` → `entity-international`, and deleted `entity-scientific-admin`.

## Triggering Role Assignment

Entity roles are assigned manually by administrators through the admin UI; entity licenses do **not** trigger automatic committee-based role changes.

### `SyncUserEntityCommitteeAction` (no-op)

This action is intentionally a no-op. Per a PM requirement, entity user roles must be managed manually through the admin interface only, so the action simply logs that it was skipped and makes no role changes. It does **not** synchronize any committee-based roles.

> A separate `SyncEntityUserRolesAction` does sync the base `entity-admin` role (and any roles mapped to an administered entity's active licenses via the `license_roles` pivot) for users who administer an active entity. The `entity-international` role is assigned manually.

### Key Triggers for Role Assignment

-   **Entity Creation**: When a new entity is created, the `entity-admin` role is assigned to the user who created it.
    -   *Files*: `app/Http/Controllers/Entity/EntityController.php`, `app/Http/Controllers/Federation/EntityController.php`

-   **International Certifications**: The `entity-international` role is checked when an entity attempts to purchase international certifications. This role is a prerequisite for these actions.
    -   *Files*: `src/Domain/Certifications/Actions/PurchaseCertificationAction.php`, `src/Domain/Certifications/Models/Certification.php`

-   **Diving-Specific Features**: The `entity-diving-services` role grants access to diving-related menus and attachments.
    -   *Files*: `app/Http/Controllers/Entity/EntityController.php`

-   **UI Rendering**: Views use Blade's `@role` and `@hasanyrole` directives to conditionally display UI elements based on the entity's roles. For example, certain management features use `@hasanyrole('entity-admin|entity-diving-services')`.
    -   *File*: `resources/views/web/entity/profile/edit.blade.php`
