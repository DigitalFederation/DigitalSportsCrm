# Certification Attribution Wizard Implementation

## Overview

This documentation covers the certification attribution system in the Digital Sports CRM system, which has replaced the deprecated slot-based system. The Certification Attribution Wizard is the ONLY way to acquire certifications in the system. This wizard handles the complete workflow of attributing certifications to individuals, including instructor validation, student selection, and payment processing when applicable.

## Table of Contents

1. [Architecture Changes](#architecture-changes)
2. [Database Schema](#database-schema)
3. [Domain Layer](#domain-layer)
4. [Application Layer](#application-layer)
5. [UI Components](#ui-components)
6. [Payment Flow](#payment-flow)
7. [Migration Guide](#migration-guide)
8. [API Reference](#api-reference)

## Architecture Changes

### Before (Slot-based System)
```
Federation → Purchases Slots → Distributes to Members → Creates Certification
```

### After (Attribution Wizard)
```
Entity/Federation → Select Instructor/Students → Choose Certification → Attribution → Payment (if applicable) → Activation
```

## Database Schema

### New Fields in `certification` Table

> **Note:** The table is named `certification` (singular) — see `protected $table = 'certification'` in the `Certification` model.

```sql
-- Pricing fields (current)
digital_price DECIMAL(10,2) -- Price for a digital-only certification
digital_plus_card_price DECIMAL(10,2) -- Price for digital + physical card

-- Pricing fields (legacy — retained on the table, no longer the primary model)
unit_value DECIMAL(10,2) -- General price (fallback)
unit_value_entity DECIMAL(10,2) -- Entity-specific price
unit_value_federation DECIMAL(10,2) -- Federation-specific price
tax_value DECIMAL(10,2) -- Tax amount
tax_percentage DECIMAL(5,2) -- Tax percentage
-- Note: unit_value_individual is no longer present on the table.

-- Configuration fields
is_available BOOLEAN DEFAULT true -- Whether certification is available for purchase
requester_model VARCHAR(255) DEFAULT 'all' -- Who can request: 'Individual', 'Entity', 'all'
allow_entity_group_request BOOLEAN DEFAULT false -- Allow entities to purchase for groups
requires_admin_validation BOOLEAN DEFAULT false -- Requires admin approval
```

**Note**: Internationality is determined by `committee.is_international`. See [Committee Structure](/architecture/02-committee-structure).

### Removed Tables
- `certifications_slot`
- `certifications_slot_prices`
- `certifications_slot_type`

### Modified Tables
- `certification_attributed`: Removed `slot_type_id` foreign key

## Domain Layer

### The Certification Attribution Wizard

The `CertificationAttributionWizard` is a Livewire component that manages the entire certification workflow:

#### Key Features:
- **Multi-step wizard interface** with validation at each step
- **Dual actor support**: Works for both Entity and Federation users
- **Instructor validation**: Ensures only qualified instructors can issue certifications
- **Batch processing**: Can attribute certifications to multiple students at once
- **Payment integration**: Automatically creates payment documents for paid certifications
- **Role-based restrictions**: Enforces entity-international role for international certifications

#### Wizard Steps:
1. **Context Selection**: Federation/School selection (varies by actor type)
2. **Personnel Selection**: Choose instructor, assistants, and students
3. **Certification & Details**: Select certification and provide attribution details

#### Important Methods:
```php
// Main submission method that handles the attribution
public function submit(
    CreateCertificationAttributedAction $creator,
    CalculateCertificationPriceAction $calculatePriceAction
): void

// Validates instructor qualifications
public function selectDirector(string $individualId, DetectIfIndividualIsInstructorAction $checkInstructor): void

// Updates available certifications based on instructor
private function updateCertificationsFromInstructor(): void
```

#### Supporting Actions
- `CalculateCertificationPriceAction`: Determines price based on purchaser type
- `CalculateCertificationValidityDatesAction`: Sets certification validity periods
- `BuildCertificationDocumentDetailAction`: Creates invoice line items

### State Management

Certification states follow the existing pattern:
- `PendingCertificationAttributedState`: For paid certifications awaiting payment
- `ActiveCertificationAttributedState`: For free or paid certifications
- `DirectorApprovalCertificationAttributedState`: For certifications requiring validation

## Application Layer

### Controllers

#### Entity/CertificationAttributedController
```php
// Main wizard route
GET  /entity/certification-attributed/wizard/create - Launch certification attribution wizard

// Supporting routes
GET  /entity/certifications - List certifications
GET  /entity/certifications-attributed - List attributed certifications
POST /entity/certification-attributed - Store attribution (legacy)
GET  /entity/certification-attributed/{id} - View certification details
```

### Livewire Components

#### CertificationAttributionWizard
The main component located at `app/Livewire/Certifications/CertificationAttributionWizard.php`:
- Handles both Entity and Federation workflows
- Multi-step form with validation
- Real-time instructor validation
- Dynamic certification list based on instructor qualifications
- Automatic payment document generation
- Support for federation approval bypass (for sport committee)

#### Supporting Components
- `AssistantSelectorTable`: For selecting assistant instructors
- `DirectorSelectorTable`: For selecting the main instructor
- `StudentSelectorTable`: For selecting students to receive certifications

### Events & Listeners

#### CertificationAttributedCreatedEvent
Triggered when a paid certification is ready for payment. For DirectorApproval flows, this fires after Course Director approval; otherwise it fires at creation:
```php
public function __construct(
    CertificationAttributed $certificationAttributed,
    float $price
)
```

#### CreateCertificationAttributedDocumentListener
Creates invoice documents for paid certifications:
- Generates document with type 'ORD'
- Links to certification via DocumentDetail
- Prepares for payment processing

#### ActivateAfterPaymentCertificationAttributedListener
Activates certifications after successful payment:
- Changes state to Active
- Generates certification codes
- Sets activation timestamp

## UI Components

### Views Structure
```
resources/views/
├── web/
│   └── entity/
│       └── certification_attributed/
│           ├── index.blade.php      # List of attributed certifications
│           ├── show.blade.php       # Certification details
│           └── wizard/
│               └── create.blade.php # Wizard entry point
└── livewire/
    └── certifications/
        └── certification-attribution-wizard.blade.php # Main wizard view
```

### Key UI Features
- Responsive certification grid
- Real-time price updates
- Group member selection
- Payment status indicators
- Validation warnings

## Committee Restrictions and International Certifications

> **Important:** See [Committee Structure](/architecture/02-committee-structure) for complete committee reference and internationality details.

### International Certification Rules

1. **Committee-Based Internationality**: Determined by `committee.is_international` flag
2. **Entity Role Requirements**: Only entities with `entity-international` role can purchase international certifications
3. **Validation Flow**: System checks `$certification->committee->is_international` and entity roles

## Payment Flow

### Free Certifications
```
1. User selects certification
2. Submits purchase form
3. Certification created in Active state
4. Redirect to success page
```

### Paid Certifications
```
1. User selects certification
2. Submits purchase form
3. Certification created in Pending state
4. Document (invoice) generated
5. User redirected to payment
6. After payment: Certification activated
7. Codes generated automatically
```

### Certifications Requiring Validation
```
1. User selects certification
2. Submits purchase form
3. Certification created in DirectorApproval state
4. Course Director approves/rejects
5. If approved and paid: payment document (ORD) is generated for the entity
6. Entity pays → certification remains in Waiting NF (DirectorApproved) until federation processing
```

## Usage Guide

### Accessing the Wizard

#### For Entities
```
1. Navigate to Certifications section
2. Click "Create Certification" or similar button
3. System redirects to: /entity/certification-attributed/wizard/create?filter[committee]=COMMITTEE_CODE
```

#### For Federations
```
1. Navigate to Certifications section
2. Select committee filter if needed
3. Access wizard through certification management interface
```

### Actor Types

The wizard supports two actor types:

1. **Entity Actor** (`actorType='entity'`):
   - School is pre-selected (their own entity)
   - Cannot use federation approval
   - Must select an instructor
   - Payment handled at entity level

2. **Federation Actor** (`actorType='federation'`):
   - Can select any school within federation
   - Can use federation approval (bypass instructor)
   - Can set custom validity dates
   - Requires national numbers for students

## Configuration

### Environment Variables
No new environment variables required.

### Settings
Configure in admin panel:
- Certification pricing
- Requester permissions
- Group purchase allowance
- Validation requirements

## Security Considerations

1. **Authorization**: Validated at multiple levels
   - Active affiliation required for entities
   - Entity role validation for international certifications
   - Model type restrictions enforced (Entity/Federation only)
   - Committee restrictions for international certifications

2. **Payment Security**: 
   - Documents created with unique IDs
   - State transitions logged
   - Payment gateway handles sensitive data

3. **Data Integrity**:
   - Database transactions for atomic operations
   - Foreign key constraints maintained
   - Soft deletes for audit trail
   - Role-based access control for sensitive certifications

## Troubleshooting

### Common Issues

1. **"No active affiliation" error**
   - Ensure entity has active membership
   - Check affiliation status in database

2. **"Not authorized for certification request"**
   - For regular certifications: Verify entity validation plan includes certification privileges
   - For international certifications: Verify entity has 'entity-international' role
   - Check membership package configuration

3. **"International certification not available"**
   - Verify entity has 'entity-international' role
   - Check certification's `committee.is_international` (see [Committee Structure](/architecture/02-committee-structure))

4. **Payment not activating certification**
   - Verify DocumentDetail links correctly
   - Check ActivateAfterPayment event is firing
   - Review payment gateway logs

### Debug Commands

```bash
# Check certification configuration
php artisan tinker
>>> Certification::find(1)->toArray();

# Verify entity privileges
>>> $entity = Entity::find(1);
>>> $entity->hasActiveAffiliation();
>>> $entity->hasRole('entity-international');

# Check international certification availability (via committee)
>>> $cert = Certification::find(1);
>>> $cert->isInternationalCertification(); // Helper method
>>> $cert->committee->code; // SPORT, DIVING, DIVINGSERVICES, SCIENTIFIC

# Test purchase flow
>>> $cert = Certification::find(1);
>>> $entity = Entity::find(1);
>>> app(PurchaseCertificationAction::class)($cert, $entity, ['individual_id' => 1]);
```

## Future Enhancements

1. **Bulk Import**: Admin tool for bulk certification creation
2. **Discounts**: Promotional codes and volume discounts
3. **Prerequisites**: Automatic prerequisite certification checks
4. **Renewals**: Automated renewal reminders and processing
5. **Analytics**: Purchase trends and revenue reporting

## Support

For issues or questions:
1. Check this documentation
2. Review error logs in `storage/logs`
3. Contact development team

---

Last Updated: January 7, 2026
Version: 1.1.0 (Updated for committee-based internationality)
