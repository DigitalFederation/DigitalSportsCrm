# Individual Namespace: Roles and Permissions

This document outlines the roles available for the `Individual` namespace, their permissions, and how they are triggered within the system. This information is derived from the `RoleAndPermissionSeeder` and its usage throughout the application.

## Summary of Individual Roles

The `Individual` namespace has a set of roles that grant specific permissions to users. These roles are assigned based on the individual's professional roles and qualifications.

### Core Individual Roles

-   `individual-approved`: A foundational role for an approved individual. Grants access to the starter menu, orders, licenses, and official documents.
-   `individual-coach`: For coaches. Grants access to the coach menu, events, and sport-specific licenses and documents.
-   `individual-instructor`: For instructors. Grants access to the instructor menu, events, and diving-related licenses and documents.
-   `individual-athlete`: For athletes. Grants access to the athlete menu, events, and sport-specific licenses and documents.
-   `individual-technical-official`: For technical officials (judges and referees). Grants access to both judge and referee menus, events, and sport-specific licenses and documents.
-   `individual-leader`: For leaders. Grants access to the leader menu, events, and diving-related licenses and documents.
-   `individual-diver`: For divers. Grants access to the diver menu, events, and diving-related licenses and documents.
-   `individual-scientific`: For scientific divers. Grants access to the scientific menu, events, and related licenses and documents.
-   `individual-sport`: For sport-focused individuals. Grants access to the sport menu, events, and related licenses and documents.
-   `individual-lms-instructor`: For LMS instructors. Grants access to the LMS menu and student progression management.

### View Roles

-   `view-individual-coach`: Grants view access to the coach and sport menus.
-   `view-individual-technical-official`: Grants view access to the judge, referee, and sport menus.
-   `view-individual-diving-instructor`: Grants view access to the diver and instructor menus.
-   `view-individual-scientific-instructor`: Grants view access to the scientific and instructor menus.
-   `view-individual-diving-leader`: Grants view access to the diver and leader menus.
-   `view-individual-scientific-leader`: Grants view access to the scientific and leader menus.

## Triggering Role Assignment

The primary mechanism for assigning these roles is the `SyncUserRolesAction`. This action is responsible for synchronizing a user's roles based on their professional roles within the system.

### `SyncUserRolesAction` Logic

The `SyncUserRolesAction` is **pivot-table driven**. It does not use a hardcoded
professional-role-name-to-role map. Instead, it derives a user's roles from the role
mapping tables, collecting role IDs from:

-   **Active licenses** mapped through the `license_roles` pivot table.
-   **Active certifications** mapped through the `certification_roles` pivot table.
-   **Federation memberships** mapped through the `federation_roles` table (including
    global mappings where `federation_id` is `NULL`, and respecting the
    `requires_active_membership` flag).

The action gathers the unique role IDs from these pivots, resolves them to role names, and
calls Spatie's `syncRoles()` to set the user's roles. When an individual's licenses,
certifications, or federation memberships change, this action keeps their permissions in
sync with their current standing.

**Admin roles are preserved.** Before syncing, the action intersects the user's current
roles with a `$preservedAdminRoles` allowlist (`admin`, `federation-admin`,
`association-sport-admin`, `association-scientific-admin`, `association-admin`,
`association-territorial-admin`) and merges those back into the final role set. This is a
safeguard to prevent accidental admin lockout. (Entity roles are managed separately by
`SyncEntityUserRolesAction`.)

## Where `SyncUserRolesAction` is Triggered

The `SyncUserRolesAction` is a critical component for keeping user permissions up-to-date. It is triggered in several key places throughout the application, ensuring that role changes are reflected in response to various events:

-   **Manual Trigger**: An artisan command `sync:user-roles` exists to manually trigger the synchronization. It takes an optional `{userId?}` argument: pass a user ID to sync a single user, or omit it to sync all users. This is useful for correcting any inconsistencies or for system-wide updates.
    -   *File*: `app/Console/Commands/SyncUserRoles.php`

-   **Individual Creation**: When a new individual is created in the system, their roles are synchronized.
    -   *File*: `src/Domain/Individuals/Actions/CreateIndividualAction.php`

-   **Federation Membership**: Roles are updated when an individual's relationship with a federation changes:
    -   When an individual joins a federation.
        -   *File*: `app/Http/Controllers/Individual/FederationController.php`
    -   When a federation administrator approves an individual's request to join.
        -   *File*: `app/Http/Controllers/Federation/IndividualController.php`
    -   When a federation administrator approves or rejects an individual's request.
        -   *File*: `app/Http/Controllers/Federation/IndividualRequestController.php`

-   **License Status Changes**: An individual's roles are updated when their licenses are activated or expire.
    -   Activation: `src/Domain/Licenses/Actions/ActivateLicenseAttributedAction.php`
    -   Expiration: `src/Domain/Licenses/Actions/ExpireLicenseAttributedAction.php`
    -   A dedicated action `SyncUserRolesBasedOnLicenseAction` also exists for this purpose.

-   **Certification Status Changes**: Similarly, roles are updated when an individual's certifications are activated or expire.
    -   Activation: `src/Domain/Certifications/Actions/ActivateCertificationAttributedAction.php`
    -   Expiration: `src/Domain/Certifications/Actions/ExpireCertificationAttributedAction.php`

-   **Asynchronous Updates**: The `SyncUserRolesJob` allows for role synchronization to be performed in the background, ensuring that the user interface remains responsive during potentially long-running updates.
    -   *File*: `app/Jobs/SyncUserRolesJob.php`
