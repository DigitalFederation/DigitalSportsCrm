# Diving Professional Licensing Module

This document provides a complete overview of the Diving Professional Licensing Module, covering its features, architecture, implementation status, and testing guidelines.

---

## 1. Overview & Business Requirements

The module manages licensing and documentation for diving professionals and entities (diving schools, centers, equipment rental shops, gas filling stations) in compliance with the deployment's applicable law. It allows entities to request licenses, with validation of their technical directors and qualifications from configured certification systems.

> This document describes diving professional licensing as configured in the diving example deployment. The underlying platform is a generic federation-management system; diving is one reference configuration.

### Key Concepts

-   **License Types**: Diving School, Diving Center, Equipment Rental, Gas Filling Station.
-   **Technical Director**: A qualified diving professional responsible for an entity's technical operations.
-   **Certification Systems**: Support for multiple international diving organizations (e.g., PADI, SSI, SDI/TDI, DDI, GUE).

### Core Features

1.  **Entity License Management**: A multi-step wizard for license requests, support for multiple technical directors, and document uploads.
2.  **Technical Director System**: An invitation workflow for entities to invite professionals, who can then accept or reject the role.
3.  **Admin Validation**: A platform/federation admin panel for overseeing license requests, reviewing documents, and approving or rejecting applications.
4.  **Payment Integration**: Automatic invoice generation and license activation upon payment confirmation.
5.  **Professional Certifications**: Support for uploading and validating third-party certifications (e.g., from PADI, SSI).

---

## 2. Architecture & Implementation

The implementation strategy focuses on **extending existing systems** (licenses, documents, professional roles) rather than creating a new, separate module. This maximizes code reuse and ensures consistency.

### Database Schema

Key tables for this module:

-   `diving_professional_certifications`: Stores third-party certifications for professionals.
-   `diving_entity_technical_directors`: Manages technical directors assigned to entities and their approval workflow for license requests.

### State Management

The module uses the existing state machine patterns for:
-   **License Attributions**: `PendingValidation`, `Pending`, `Active`, `Canceled`, etc. (see [Licenses](/features/licenses) for the full state list).
-   **Director Positions** (`diving_entity_technical_directors`): status is `Assigned` (`AssignedDivingTechnicalDirectorState`) or `Removed` (`RemovedDivingTechnicalDirectorState`); the approval step is tracked via the `assigned_at`, `approved_at`, and `rejected_at` timestamp columns (with `approval_notes` / `rejection_reason`).
-   **Certifications**: `PendingValidation`, `Active`, `Expired`, `Revoked`.

### Key Workflows & UI

-   **License Request Wizard (`DivingLicenseRequestWizard`)**: A 5-step Livewire component guides entities through the license application process.
-   **Diving Professional Management (`ManageEntityDivingProfessionals` Livewire component, with `FindDivingInstructorByCode`)**: A separate system allows entities to manage their roster of associated diving professionals/instructors (looked up by code), independent of license requests.
-   **Admin Validation (`Admin\SeparatedDivingLicenseValidationController` and `Federation\SeparatedDivingLicenseValidationController`)**: Provide dedicated interfaces for platform/federation admins to review and process license applications. Entity and individual license validation are handled separately.

### API Endpoints

The module exposes REST API endpoints for managing diving licenses, certifications, and invitations across three namespaces:

-   **Entity Routes**: e.g., `/entity/diving-licenses`, `/entity/diving-licenses/{id}/directors`
-   **Individual Routes**: e.g., `/individual/diving-certifications`, `/individual/technical-director-positions/{id}/approve` (route name `individual.technical_director_positions.*`)
-   **Admin Routes**: e.g., `/admin/entity-diving-license-validation` and `/admin/individual-diving-license-validation` (entity vs. individual validation are split), `/admin/diving-professional-certifications/{id}/approve`

---

## 3. Implementation Scope

The module includes the core workflows for:
- Entity license request wizard
- Technical director management and approval
- Admin validation workflow
- Payment integration via EasyPay
- Full state machine with all transitions

---

## 4. Testing Guide

### Setup

1.  **Load Schema**: `php artisan migrate:fresh` (the database structure is loaded from the squashed schema dump in `database/schema/mysql-schema.sql`).
2.  **Run Seeders**:
    *   `php artisan db:seed --class=DivingEntityLicenseSeeder`
    *   `php artisan db:seed --class=DivingProfessionalRoleSeeder`
3.  **Clear Caches**: `php artisan config:clear && php artisan cache:clear`

### Testing Scenarios

1.  **Individual: Upload Certification**
    *   Log in as an Individual.
    *   Navigate to "Diving Professional" -> "Diving Certifications".
    *   Upload a third-party certification (e.g., PADI). Verify it appears in a "Pending Validation" state.

2.  **Entity: Request License**
    *   Log in as an Entity.
    *   Navigate to "Diving License Management" -> "Request New License".
    *   Use the wizard to select a license type and invite one or more technical directors by their member code.
    *   Verify the sent invitations appear in the "Invitations" tab.

3.  **Individual: Accept Invitation**
    *   Log in as the invited Individual.
    *   Navigate to "Technical Director Positions" (`/individual/technical-director-positions`).
    *   Approve the pending position.

4.  **Admin: Validate License**
    *   Log in as a platform/federation admin.
    *   Navigate to the diving license validation queue.
    *   Review the submitted application, including the accepted director.
    *   Approve the license. If it's a paid license, verify its state changes to `Pending Payment`. If it's a free license, verify it becomes `Active`.

### Verification Points

-   **Database**: Check the `diving_professional_certifications` and `diving_entity_technical_directors` tables for correct data.
-   **UI**: Ensure menu items, forms, and status badges appear correctly.
-   **Authorization**: Confirm that users can only access their own data and actions.
-   **Notifications**: Verify that email/in-app notifications are sent for invitations and status changes.
