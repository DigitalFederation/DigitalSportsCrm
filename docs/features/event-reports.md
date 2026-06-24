# Event Reports System

This document outlines the event reporting system, which enables Technical Delegates and Chief Technical Officials to submit post-event reports with referee tracking and document attachments.

---

## 1. Overview

Each event has two distinct reports that must be completed by assigned officials:

| Report Type | Responsible Role | Purpose |
|-------------|------------------|---------|
| **Technical Delegate Report** | Technical Delegate | Overall event assessment and incident documentation |
| **Chief Technical Official Report** | Chief Judge | Referee presence tracking and function assignments |

Both reports support:
- Draft saving (multiple saves before submission)
- Document attachments (PDF, DOC, DOCX, JPG, JPEG, PNG up to 10MB)
- Once submitted, reports become read-only

All routes are consolidated under the `/individual/technical-delegate` prefix with a single controller.

---

## 2. Technical Delegate Report

The Technical Delegate submits a report covering all aspects of the competition.

### Report Fields

| Field | Description |
|-------|-------------|
| `participants_withdrawals` | Number of participants (athletes, coaches, team officials) and withdrawals/no-shows |
| `incidents_occurrences` | Summary of accidents and sanctions during the event |
| `officials_performance` | General observations about the technical officiating team performance |
| `facilities_evaluation` | Assessment of facilities, support areas, and weather conditions |
| `safety_first_aid` | Notes about safety officer and emergency/first aid resources |
| `anti_doping_control` | Whether doping tests occurred and number of athletes tested |
| `sports_protests` | Documentation of any sports protests and their circumstances |
| `observations_recommendations` | General observations and improvement suggestions for future events |

### Access Control

Only individuals assigned as **Technical Delegate** (`technical_delegate` role in `evt_event_roles`) for the specific event can access and submit this report.

### Event Enrollment View

The Technical Delegate has read-only access to view all event enrollments via the enrollments page (`/individual/technical-delegate/{event}/enrollments`).

**Available Tabs:**

| Tab | Columns Displayed |
|-----|-------------------|
| **Athletes** | Name, Gender, Birth Date, Entity, Discipline, Attributes, Status |
| **Coaches** | Name, Gender, Birth Date, Entity, Attributes |
| **Referees** | Name, Gender, Birth Date, Email, Phone, Attributes |
| **Officials** | Name, Gender, Birth Date, Entity, Attributes |
| **Staff** | Name, Gender, Birth Date, Email, Phone, Attributes |

Each tab shows a paginated table with export functionality.

### Routes

| Route | Method | Description |
|-------|--------|-------------|
| `individual.technical-delegate.index` | GET | List events (TD + CJ combined history) |
| `individual.technical-delegate.enrollments` | GET | View event enrollment data (read-only) |
| `individual.technical-delegate.enrollments.export` | GET | Export enrollment data |
| `individual.technical-delegate.td-report` | GET | View/edit report form |
| `individual.technical-delegate.td-report.save` | POST | Save draft |
| `individual.technical-delegate.td-report.submit` | POST | Submit final report |
| `individual.technical-delegate.td-report.upload` | POST | Upload attachment |
| `individual.technical-delegate.td-report.document.delete` | DELETE | Remove attachment |
| `individual.technical-delegate.td-report.document.download` | GET | Download attachment |

---

## 3. Chief Technical Official Report

The Chief Judge manages referee tracking and submits a technical considerations report.

### Report Components

1. **Referee Presence Registry**
   - Checkbox list of all enrolled referees
   - Tracks attendance (present/absent) for each referee
   - Presence data stored in `evt_referee_function_assignments.is_present`

2. **Function Assignments**
   - Chief Judge assigns one or more functions to each referee
   - A referee can perform **multiple functions** in the same competition
   - Functions can be:
     - Selected from sport-specific predefined list (`evt_referee_functions`)
     - Custom text entry for non-standard functions

3. **Technical Considerations**
   - Free-text field for technical observations about the competition

4. **Document Attachments**
   - Same functionality as Technical Delegate report

### Sport-Specific Referee Functions

Referee functions are defined per sport/discipline in the `evt_referee_functions` table.

**Example Functions:**
- Secretary
- Starts
- Pre-Start Assistant
- Timekeeper
- Turns
- Aquatic

Each sport can have its own set of functions, managed by federation administrators.

### Access Control

Only individuals assigned as **Chief Judge** (`chief_judge` role in `evt_event_roles`) for the specific event can access and submit this report.

### Routes

| Route | Method | Description |
|-------|--------|-------------|
| `individual.technical-delegate.referees` | GET | View/manage referee functions |
| `individual.technical-delegate.referees.export` | GET | Export referee data |
| `individual.technical-delegate.assign-function` | POST | Assign function to referee |
| `individual.technical-delegate.remove-function` | DELETE | Remove function assignment |
| `individual.technical-delegate.presence` | POST | Update referee presence |
| `individual.technical-delegate.cj-report` | GET | View/edit report form |
| `individual.technical-delegate.cj-report.save` | POST | Save draft |
| `individual.technical-delegate.cj-report.submit` | POST | Submit final report |
| `individual.technical-delegate.cj-report.upload` | POST | Upload attachment |
| `individual.technical-delegate.cj-report.document.delete` | DELETE | Remove attachment |
| `individual.technical-delegate.cj-report.document.download` | GET | Download attachment |

---

## 4. Database Schema

### Technical Delegate Reports

```sql
CREATE TABLE evt_technical_delegate_reports (
    id BIGINT PRIMARY KEY,
    event_id BIGINT UNIQUE,           -- One report per event
    submitted_by CHAR(36),            -- Individual who created/submitted
    participants_withdrawals TEXT,
    incidents_occurrences TEXT,
    officials_performance TEXT,
    facilities_evaluation TEXT,
    safety_first_aid TEXT,
    anti_doping_control TEXT,
    sports_protests TEXT,
    observations_recommendations TEXT,
    is_submitted BOOLEAN DEFAULT FALSE,
    submitted_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Chief Judge Reports

```sql
CREATE TABLE evt_chief_judge_reports (
    id BIGINT PRIMARY KEY,
    event_id BIGINT UNIQUE,           -- One report per event
    submitted_by CHAR(36),            -- Individual who created/submitted
    technical_considerations TEXT,
    is_submitted BOOLEAN DEFAULT FALSE,
    submitted_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Referee Functions (Sport-Specific)

```sql
CREATE TABLE evt_referee_functions (
    id BIGINT PRIMARY KEY,
    sport_id BIGINT,                  -- Links to evt_sports
    function_name VARCHAR(255),
    function_code VARCHAR(50),        -- Short code (optional)
    description TEXT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Referee Function Assignments

```sql
CREATE TABLE evt_referee_function_assignments (
    id BIGINT PRIMARY KEY,
    event_id BIGINT,
    referee_enrollment_id BIGINT,     -- Links to evt_referees_enrollment
    is_present BOOLEAN DEFAULT TRUE,  -- Presence tracking
    referee_function_id BIGINT,       -- Optional: predefined function
    function_text TEXT,               -- Optional: custom function text
    assigned_by CHAR(36),             -- Chief Judge individual_id
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
-- Note: No unique constraint - allows multiple functions per referee per event
```

### Report Documents (Polymorphic)

```sql
CREATE TABLE evt_event_report_documents (
    id BIGINT PRIMARY KEY,
    documentable_type VARCHAR(255),   -- Report model class
    documentable_id BIGINT,           -- Report ID
    file_name VARCHAR(255),
    file_path VARCHAR(255),
    file_size BIGINT,
    mime_type VARCHAR(255),
    uploaded_by UUID,                 -- Individual who uploaded
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 5. Domain Actions

### Technical Delegate Report

| Action | Purpose |
|--------|---------|
| `SaveTechnicalDelegateReportAction` | Save/update report draft |
| `SubmitTechnicalDelegateReportAction` | Finalize and lock report |
| `UploadReportDocumentAction` | Add document attachment |

### Chief Judge Report

| Action | Purpose |
|--------|---------|
| `SaveChiefJudgeReportAction` | Save/update report draft |
| `SubmitChiefJudgeReportAction` | Finalize and lock report |
| `UpdateRefereePresenceAction` | Update attendance checkboxes |
| `AssignRefereeFunctionAction` | Assign function to referee |

---

## 6. Workflow

### Report Lifecycle

```
Draft (editable)
    | Save Draft (can repeat)
    v
Draft (with content)
    | Submit
    v
Submitted (read-only, timestamped)
```

### Key Business Rules

1. **One Report Per Event**: Each event can have at most one Technical Delegate report and one Chief Judge report (enforced by unique constraint on `event_id`)

2. **Role-Based Access**: Reports are only accessible to the individual assigned to the corresponding role for that specific event

3. **Immutable After Submission**: Once `is_submitted = true`, the report and its attachments cannot be modified

4. **Multiple Functions Per Referee**: A referee can be assigned multiple functions in the same event (e.g., "Starts" and "Turns")

5. **Presence Without Function**: A referee can be marked as present without being assigned any specific function (creates a presence-only record)

---

## 7. Key Files

### Models

- `src/Domain/EvtEvents/Models/TechnicalDelegateReport.php`
- `src/Domain/EvtEvents/Models/ChiefJudgeReport.php`
- `src/Domain/EvtEvents/Models/EventReportDocument.php`
- `src/Domain/EvtEvents/Models/RefereeFunction.php`
- `src/Domain/EvtEvents/Models/RefereeFunctionAssignment.php`

### Controller

- `app/Http/Controllers/Individual/TechnicalDelegateController.php`

### Livewire Components

- `app/Livewire/EvtEvents/TechnicalTeamHistory.php`
- `app/Livewire/EvtEvents/DelegateEnrollments.php`
- `app/Livewire/EvtEvents/JudgeEnrollments.php`
- `app/Livewire/EvtEvents/OfficialHistory.php`

### Actions

- `src/Domain/EvtEvents/Actions/SaveTechnicalDelegateReportAction.php`
- `src/Domain/EvtEvents/Actions/SubmitTechnicalDelegateReportAction.php`
- `src/Domain/EvtEvents/Actions/SaveChiefJudgeReportAction.php`
- `src/Domain/EvtEvents/Actions/SubmitChiefJudgeReportAction.php`
- `src/Domain/EvtEvents/Actions/UpdateRefereePresenceAction.php`
- `src/Domain/EvtEvents/Actions/AssignRefereeFunctionAction.php`
- `src/Domain/EvtEvents/Actions/UploadReportDocumentAction.php`

### Views

- `resources/views/web/individual/technical_delegate/index.blade.php`
- `resources/views/web/individual/technical_delegate/enrollments.blade.php`
- `resources/views/web/individual/technical_delegate/td-report.blade.php`
- `resources/views/web/individual/technical_delegate/referees.blade.php`
- `resources/views/web/individual/technical_delegate/cj-report.blade.php`
- `resources/views/components/evt_event/report-document-upload.blade.php`

### Tests

- `tests/Feature/Individual/TechnicalDelegateReportTest.php`
- `tests/Feature/Individual/ChiefJudgeReportTest.php`
- `tests/Feature/Livewire/EvtEvents/JudgeEnrollmentsTest.php`
- `tests/Unit/EvtEvents/TechnicalDelegateReportModelTest.php`
- `tests/Unit/EvtEvents/ChiefJudgeReportModelTest.php`

---

## 8. Future Enhancements

The current implementation uses text-based function assignments. Future improvements may include:

1. **Official Records Integration**: Display function history in individual referee profiles
2. **Statistics Dashboard**: Aggregate function assignments across events for referee performance tracking
3. **PDF Report Generation**: Export formatted reports for printing/archiving
4. **Notification System**: Alert officials when assigned to event roles
