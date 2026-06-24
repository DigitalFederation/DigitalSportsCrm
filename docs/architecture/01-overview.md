---
title: Technical Architecture
description: Core principles, DDD structure, model relationships, and design patterns
---

# Digital Sports CRM Technical Architecture

An overview of the architecture, domain models, and design patterns used in Digital Sports CRM.

---

## 1. Introduction & Core Principles

This directory contains documentation about the architecture and model relationships in the Digital Sports CRM application. This documentation is designed to help:

1.  **New Developers** - Quickly understand the system architecture and model relationships
2.  **LLM Assistants** - Provide context for AI tools to generate more accurate code and suggestions
3.  **Existing Team Members** - Reference complex relationships across domains

### Domain-Driven Design (DDD)

The application follows Domain-Driven Design (DDD) principles, with code organized by business domains rather than technical concerns. Each domain has its own models, actions, data transfer objects, and state classes.

The code is organized in the following structure:

```
src/
├── Domain/               # Domain layer with business logic
│   ├── Attachments/
│   ├── Certifications/   # Certification-related models and logic
│   ├── Diagnostics/
│   ├── Diving/           # Diving professional certifications & technical directors
│   ├── Documents/
│   ├── Entities/         # Organizations/clubs management
│   ├── EventApplications/
│   ├── EvtEvents/        # Events and competitions
│   ├── Federations/      # Federation management
│   ├── Geographic/
│   ├── Imports/
│   ├── Individuals/      # Person management
│   ├── Insurance/
│   ├── Invoicing/
│   ├── Licenses/         # License management
│   ├── Memberships/      # Membership plans and subscriptions
│   ├── Menus/
│   ├── OfficialDocuments/
│   ├── Payments/
│   ├── Permissions/
│   ├── Reports/
│   └── Users/
└── Support/              # Cross-cutting concerns and utilities
```

Each domain subdirectory typically contains:
- `Actions/` - Service classes for domain operations
- `DataTransferObject/` - DTOs for data transformation
- `Models/` - Eloquent models
- `States/` - State classes for workflow management

### Key Architectural Concepts

#### Hierarchical Organization Structure
The system models a hierarchical organization structure:
1.  **Federations** - Top-level governing bodies, which can have parent-child relationships (international federation → national federations → local federations)
2.  **Entities** - Organizations/clubs that belong to federations
3.  **Individuals** - People who can be members of entities and federations

#### Polymorphic Relationships
Several models use polymorphic relationships to associate with different model types:
- `LicenseAttributed` can be associated with either `Individual` or `Entity` models
- `Organizer` can be associated with either `Federation` or `Entity` models

When analyzing or generating code, be aware of the `model_type` and `model_id` fields that implement these polymorphic relationships.

#### State Pattern Implementation
The application uses a state pattern for managing workflows, particularly for:
- License attributions
- Certification attributions
- Entity-federation relationships
- Individual-federation relationships

States are stored as fully qualified class names in the database (e.g., `Domain\Licenses\States\ActiveLicenseAttributedState::class`).

#### Many-to-Many Relationships with Extra Data
Many relationships in the system are many-to-many with additional pivot data:
- `IndividualEntity` connects individuals to entities with status information
- `IndividualFederation` connects individuals to federations with status information
- `EntityFederation` connects entities to federations with status information

#### UUID Primary Keys
Most models use UUID primary keys rather than auto-incrementing integers, particularly for:
- `Individual`
- `Entity`
- `Federation`
- `LicenseAttributed`
- `CertificationAttributed`

#### Committee-Based Organization
The system organizes many aspects by committees. See [Committee Structure](/architecture/02-committee-structure) for complete reference.

| Committee | `is_international` | Description |
|-----------|-------------------|-------------|
| `SPORT` | `false` | National underwater sports |
| `DIVINGSERVICES` | `false` | National diving services |
| `DIVING` | `true` | International diving |
| `SCIENTIFIC` | `true` | International scientific |

> The committee codes above come from the diving reference deployment; they are configured data, not platform built-ins.

Methods often filter by committee, e.g., `$individual->certificationsDivingAttributed()`.

---

## 2. Model Relationships

How the core domain models relate to one another.

### Core Domains and Models

#### Individuals Domain
The Individuals domain manages all person-related data and relationships.
- **Key Models:** `Individual`, `IndividualEntity`, `IndividualFederation`, `IndividualProfessionalRole`, `ProfessionalRole`
- **Relationships:** An Individual can belong to many Entities and Federations, have many professional roles, certifications, and licenses, and participate in various events.

#### Entities Domain
The Entities domain manages organizations and their relationships.
- **Key Models:** `Entity`, `EntityFederation`, `EntityProfessionalRole`, `EntityAthlete`
- **Relationships:** An Entity can belong to many Federations, have many Individuals as members, have professionals with specific roles, and have licenses.

#### Federations Domain
The Federations domain manages governing bodies and their hierarchical structure.
- **Key Models:** `Federation`, `FederationProfessionalRole`
- **Relationships:** A Federation can have a parent-child hierarchy, manage Entities and Individuals, and handle certifications, licenses, and memberships.

#### Licenses Domain
The Licenses domain manages permissions and authorization aspects.
- **Key Models:** `License`, `LicenseAttributed`, `LicenseType`
- **Relationships:** A License belongs to a Committee, ProfessionalRole, and LicenseType. It can be attributed to Individuals or Entities (polymorphic) and uses a state pattern for its workflow.

#### Certifications Domain
The Certifications domain manages qualifications and their attribution.
- **Key Models:** `Certification`, `CertificationAttributed`
- **Relationships:** A Certification belongs to a Committee and ProfessionalRole, is linked to a License, can have parent-child relationships, and its attribution uses a state pattern.

#### Events Domain (EvtEvents)
The Events domain manages competitions, enrollments, and participation records.
- **Key Models:** `AthleteEnrollment`, `CoachEnrollment`, `RefereeEnrollment`, `TeamOfficialEnrollment`, `CompetitionReferee`, `Organizer`
- **Relationships:** Various enrollment types are linked to Individuals. Events can be organized by Federations or Entities (polymorphic).

#### Memberships Domain
The Memberships domain manages federation memberships and plans.
- **Key Models:** `Membership`, `MembershipPlan`, `LocalMembershipPlan`
- **Relationships:** A Membership belongs to a Federation and can have many MembershipPlans, which can include multiple Licenses.

### Database Schema Conventions
- **Primary Keys:** UUID is used as the primary key for most models.
- **Relationships:** Many-to-many relationships often include additional pivot data, especially status information.
- **State Management:** State classes are stored as fully qualified class names in the database.
- **Auditing:** Most models include audit fields (`created_by`, `updated_by`, timestamps).

### Visual Representation (Mermaid Diagrams)
The model relationships are visualized through several Mermaid diagrams:
1.  Core Models Relationship Diagram
2.  License and Certification Models
3.  Events and Enrollments Models
4.  Memberships and Plans Models
5.  Complete Domain Model Relationships

---

## 3. Model Usage Examples

This section provides practical examples of how models interact.

### Individual Relationships
**Finding an Individual's Federations:**
```php
// Get all federations an individual belongs to
$individual = Individual::find($id);
$federations = $individual->federations;

// Get only active federation relationships
$activeFederations = $individual->individualFederations()
    ->where('status_class', ActiveIndividualFederationState::class)
    ->with('federation')
    ->get()
    ->pluck('federation');
```
**Finding an Individual's Active Certifications:**
```php
// Get all active certifications for an individual
$individual = Individual::find($id);
$activeCertifications = $individual->certificationsAttributed()
    ->where('status_class', ActiveCertificationAttributedState::class)
    ->with('certification')
    ->get();
```

### Entity Relationships
**Finding Entities in a Specific Federation:**
```php
// Get all entities that belong to a specific federation
$federation = Federation::find($id);
$entities = $federation->entities;

// Using the scope method
$entities = Entity::filterFederation($federationId)->get();
```

### License Management
**Attributing a License to an Individual:**

> Polymorphic `model_type` values use the registered morph map aliases (`'individual'`, `'entity'`, `'federation'`, registered in `AppServiceProvider`), not fully qualified class names. Scopes query e.g. `where('model_type', 'individual')`.

```php
// Create a new license attribution
$licenseAttributed = LicenseAttributed::create([
    'license_id' => $licenseId,
    'federation_id' => $federationId,
    'model_type' => 'individual',
    'model_id' => $individualId,
    'status_class' => ActiveLicenseAttributedState::class,
    'license_name' => $license->name,
    'holder_name' => $individual->full_name,
    'federation_name' => $federation->name,
    'date_begin' => now(),
    'date_expire' => now()->addYears(1),
]);
```

### State Pattern Usage
The application uses the State Pattern for managing workflows.
```php
// Example: Changing a license state from pending to active
$licenseAttributed = LicenseAttributed::find($id);
$licenseAttributed->state->transitionTo(new ActiveLicenseAttributedState($licenseAttributed));

// Example: Checking a certification state
$certificationAttributed = CertificationAttributed::find($id);
if ($certificationAttributed->isActive()) {
    // Perform actions for active certifications
}
```

### Filtering and Scopes
The application makes extensive use of query scopes for filtering data:
```php
// Filter individuals by federation
$individuals = Individual::filterFederation($federationId)->get();

// Filter entities by country
$entities = Entity::filterCountry($countryId)->get();

// Filter certifications by committee
$certifications = Certification::filterCommittee('DIVING')->get();

// Combining multiple filters
$individuals = Individual::filterFederation($federationId)
    ->filterCountry($countryId)
    ->filterZone($geoZoneId)
    ->instructors(true)
    ->get();
```