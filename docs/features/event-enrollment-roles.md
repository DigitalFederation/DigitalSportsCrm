# Event Enrollment Roles

This document explains how individuals become eligible for enrollment in events as different roles (Athlete, Coach, Official, Referee, Staff).

---

## Quick Reference: What's Needed for Each Role

| Role | What the Person Needs | Where to Set It Up |
|------|----------------------|-------------------|
| **Athlete** | Active membership + registered as athlete for the sport | Entity > Athletes |
| **Coach** | Active membership + assigned as coach for the sport | Entity > Coaches |
| **Official** | Active membership only | No setup needed |
| **Referee** | Active membership + **referee certification activated** | Federation > Certifications |
| **Staff** | Active membership only | No setup needed |

---

## How to Create an Athlete

**Goal:** Make a person selectable as an athlete when enrolling for events.

### Step-by-Step

1. **Login to the Entity panel**
   - URL: `/entity/dashboard`

2. **Go to Athletes page**
   - URL: `/entity/athletes`
   - Menu: Entity sidebar > Athletes

3. **Add the person as an athlete**
   - Click "Add Athlete" or similar button
   - Select the individual from the list
   - Select the sport they will compete in
   - Save

4. **Verify**
   - The person should now appear in the Athletes list
   - They will appear in athlete enrollment lists for events in that sport

### Requirements
- The person must already be a **member of the entity** with **Active** status
- They must be registered for the **specific sport** the event is for

---

## How to Create a Coach

**Goal:** Make a person selectable as a coach when enrolling for events.

### Step-by-Step

1. **Login to the Entity panel**
   - URL: `/entity/dashboard`

2. **Go to Coaches page**
   - URL: `/entity/coaches`
   - Menu: Entity sidebar > Coaches

3. **Add the person as a coach**
   - Click "Add Coach" or "Invite Coach"
   - Select the individual from the list
   - Select the sport they will coach
   - Save

4. **Verify**
   - The person should now appear in the Coaches list
   - They will appear in coach enrollment lists for events in that sport

### Requirements
- The person must already be a **member of the entity** with **Active** status
- They must be assigned to the **specific sport** the event is for

### Note for Federation Enrollments
When a **Federation** enrolls coaches (not an Entity), any active federation member can be enrolled as a coach - no sport assignment needed.

---

## How to Create an Official (Team Official)

**Goal:** Make a person selectable as a team official when enrolling for events.

### Step-by-Step

**No special setup required!**

Any person who is an **active member** of the federation or entity can be enrolled as a team official.

### To Verify Someone Can Be an Official

1. **Go to Members list**
   - Entity: `/entity/individuals`
   - Federation: `/federation/individuals`

2. **Find the person and check their status**
   - Status must be **Active**

3. **Done**
   - If they are an active member, they can be enrolled as an official

---

## How to Create a Staff Member

**Goal:** Make a person selectable as staff when enrolling for events.

### Step-by-Step

**No special setup required!**

Any person who is an **active member** of the federation or entity can be enrolled as event staff.

### To Verify Someone Can Be Staff

1. **Go to Members list**
   - Entity: `/entity/individuals`
   - Federation: `/federation/individuals`

2. **Find the person and check their status**
   - Status must be **Active**

3. **Done**
   - If they are an active member, they can be enrolled as staff

### Difference Between Staff and Officials

| Role | Purpose | Typical Examples |
|------|---------|-----------------|
| **Official** | Team officials who accompany athletes/coaches | Team managers, physiotherapists |
| **Staff** | Event organization staff | Volunteers, logistics, media, medical |

---

## How to Create a Referee

**Goal:** Make a person selectable as a referee when enrolling for events.

This is the most complex role because it requires a **certification**.

### Step-by-Step

#### Step 1: Ensure the Person is a Federation Member

1. **Go to Federation Members**
   - URL: `/federation/individuals`
   - Menu: Federation sidebar > Members

2. **Find the person and verify status is Active**
   - If they're not a member, add them first
   - If status is not Active, activate their membership

#### Step 2: Find a Referee Certification

1. **Go to the certification catalog (admin)**
   - URL: `/admin/certifications` (as platform/federation admin)
   - This shows all available certifications

2. **Identify a referee certification**
   - Look for certifications with "Referee" in the name
   - Examples:
     - "Referee Level 1"
     - "Regional Referee"

3. **Verify the certification grants referee status**
   - The certification must be linked to a Professional Role with role `TECHNICAL_OFFICIAL` (the professional-role value that grants referee eligibility)
   - If no referee certification exists, one must be created by a platform/federation admin

#### Step 3: Attribute the Certification to the Person

1. **Go to Federation Certifications**
   - URL: `/federation/certifications-attributed`
   - Menu: Federation sidebar > Certifications

2. **Create new certification attribution**
   - Click "Add Certification" or "Attribute Certification"
   - Select the **referee certification** identified in Step 2
   - Select the **individual** who will become a referee
   - Fill in required fields
   - Save

#### Step 4: Activate the Certification

1. **Find the certification in the list**
   - It will show status "Pending" initially

2. **Activate it**
   - Click on the certification
   - Click "Activate" button
   - The system will automatically:
     - Change status to "Active"
     - Assign the `TECHNICAL_OFFICIAL` professional role to the person

#### Step 5: Verify

1. **Check the person's profile**
   - Go to their individual profile
   - Look at "Professional Roles" section
   - You should see a referee role listed (a `TECHNICAL_OFFICIAL` professional role)

2. **Test in event enrollment**
   - Go to an event enrollment page
   - Click on "Referee" enrollment
   - The person should now appear in the list

---

## Troubleshooting: Person Not Showing in Enrollment List?

### Athlete Not Showing

| Check | How to Verify | Fix |
|-------|--------------|-----|
| Active member? | Entity > Members > Find person > Check status | Activate membership |
| Registered as athlete? | Entity > Athletes > Search for person | Add as athlete |
| Correct sport? | Entity > Athletes > Check sport column | Re-add for correct sport |
| Already enrolled? | Event > Review enrollments | Already in the event |
| Gender match? | Discipline settings | Choose correct discipline |

### Coach Not Showing

| Check | How to Verify | Fix |
|-------|--------------|-----|
| Active member? | Entity > Members > Find person > Check status | Activate membership |
| Registered as coach? | Entity > Coaches > Search for person | Add as coach |
| Correct sport? | Entity > Coaches > Check sport column | Re-add for correct sport |
| Already enrolled? | Event > Review enrollments | Already in the event |

### Official Not Showing

| Check | How to Verify | Fix |
|-------|--------------|-----|
| Active member? | Federation/Entity > Members > Check status | Activate membership |
| Already enrolled? | Event > Review enrollments | Already in the event |

### Staff Not Showing

| Check | How to Verify | Fix |
|-------|--------------|-----|
| Active member? | Federation/Entity > Members > Check status | Activate membership |
| Already enrolled? | Event > Review enrollments | Already in the event |

### Referee Not Showing (Most Common Issues)

| Check | How to Verify | Fix |
|-------|--------------|-----|
| Active federation member? | Federation > Members > Check status | Activate membership |
| Has referee certification? | Individual profile > Certifications | Attribute certification |
| Certification is ACTIVE? | Federation > Certifications > Check status | **Activate the certification** |
| Has `TECHNICAL_OFFICIAL` professional role? | Individual profile > Professional Roles | Activation should auto-assign |
| Already enrolled? | Event > Review enrollments | Already in the event |

**Most common issue:** The certification exists but was never **activated**. Pending certifications don't grant the referee role.

---

## Understanding the Referee Certification Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    REFEREE SETUP FLOW                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. CERTIFICATION EXISTS                                        │
│     └── Admin links certification to TECHNICAL_OFFICIAL role    │
│                          │                                      │
│                          ▼                                      │
│  2. CERTIFICATION ATTRIBUTED                                    │
│     └── Federation attributes certification to individual       │
│     └── Status: PENDING                                         │
│     └── Person does NOT appear in referee lists yet             │
│                          │                                      │
│                          ▼                                      │
│  3. CERTIFICATION ACTIVATED    ◄── THIS IS THE KEY STEP         │
│     └── Admin clicks "Activate"                                 │
│     └── Status: ACTIVE                                          │
│     └── System auto-assigns TECHNICAL_OFFICIAL professional role│
│                          │                                      │
│                          ▼                                      │
│  4. PERSON IS NOW A REFEREE                                     │
│     └── Appears in referee enrollment lists                     │
│     └── Can be enrolled in events as referee                    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## URL Reference

| Page | URL | Used For |
|------|-----|----------|
| Entity Dashboard | `/entity/dashboard` | Entity home |
| Entity Members | `/entity/individuals` | View/manage entity members |
| Entity Athletes | `/entity/athletes` | Add/remove athletes |
| Entity Coaches | `/entity/coaches` | Add/remove coaches |
| Federation Dashboard | `/federation/dashboard` | Federation home |
| Federation Members | `/federation/individuals` | View/manage federation members |
| Federation Certifications | `/federation/certifications-attributed` | Manage certifications |
| Event Enrollment | `/federation/evt-events/events/{id}/enrollment/{type}` | Enroll participants |

---

---

# Technical Reference (For Developers)

The sections below contain technical implementation details.

---

## Database Tables

| Role | Key Tables |
|------|-----------|
| Athlete | `entity_athletes` (entity_id, individual_id, sport_id) |
| Coach | `entity_professional_role` (entity_id, individual_id, professional_role_id, sport_id) |
| Official | `individual_federation` / `individual_entity` (membership only) |
| Referee | `individual_professional_role` (individual_id, professional_role_id where role='TECHNICAL_OFFICIAL') |
| Staff | `individual_federation` / `individual_entity` (membership only) |

## Enrollment Tables

| Role | Enrollment Table |
|------|-----------------|
| Athlete | `evt_athletes_enrollment` |
| Coach | `evt_coaches_enrollment` |
| Official | `evt_officials_enrollment` |
| Referee | `evt_referees_enrollment` |
| Staff | `evt_staff_enrollment` |

## Key Query Logic

### Athletes
```php
// Entity enrollment: requires entity_athletes registration
$query->whereHas('entityAthletes', fn($q) => $q->where('entity_id', $entityId)->where('sport_id', $sportId))
```

### Coaches
```php
// Entity enrollment: requires entity_professional_role with COACH role
$query->whereDoesntHave('coachEnrollments', ...)
```

### Officials
```php
// Just active membership
$query->whereDoesntHave('officialsEnrollments', ...)
```

### Referees
```php
// Requires TECHNICAL_OFFICIAL professional role
$query->whereHas('professionalRoles', fn($q) => $q->where('role', 'TECHNICAL_OFFICIAL'))
      ->whereDoesntHave('refereeEnrollments', ...)
```

## Certification Activation

When a certification is activated via `ActivateCertificationAttributedAction`:

```php
if (! empty($certificationAttributed->certification->professional_role_id)) {
    $professionalRoleAction($individual, $certificationAttributed->certification->professional_role_id);
}
```

This creates a record in `individual_professional_role`, making the person eligible for referee enrollment.

## Key Files

| File | Purpose |
|------|---------|
| `app/Livewire/EvtEvents/ManageEnrollment.php` | Main enrollment component with eligibility queries |
| `src/Domain/EvtEvents/Actions/CreateRefereeEnrollmentAction.php` | Creates referee enrollments |
| `src/Domain/EvtEvents/Actions/CreateCoachEnrollmentAction.php` | Creates coach enrollments |
| `src/Domain/EvtEvents/Actions/CreateTeamOfficialEnrollmentAction.php` | Creates official enrollments |
| `src/Domain/Certifications/Actions/ActivateCertificationAttributedAction.php` | Assigns professional role on certification activation |

## Testing

Tests for enrollment eligibility:
- `tests/Feature/EvtEvents/ManageRefereeEnrollmentTest.php`
- `tests/Feature/EvtEvents/ManageCoachEnrollmentTest.php`
- `tests/Feature/EvtEvents/ManageOfficialEnrollmentTest.php`
- `tests/Feature/EvtEvents/ManageEnrollmentTest.php`
