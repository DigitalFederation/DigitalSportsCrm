---
title: Event Applications
description: Event candidature system for federations and entities
---

# Event Applications

## Overview

The Event Applications system allows entities to submit candidatures (applications) to organize or host federation events. Applications can be either federation-initiated (responding to a call) or direct submissions.

---

## Application Types

### 1. Federation-Initiated
- Federation creates a template/call for applications
- Entities respond to the call
- Has submission deadline
- May have maximum number of applications

### 2. Direct Submission
- Entity submits application without a template
- More flexible timing
- Goes directly to validation

---

## Event Types

| Type | Description |
|------|-------------|
| **Organization** | General event organization (workshops, seminars) |
| **Competition** | Competitive sporting event |

---

## Application Workflow

### States

| State | Description |
|-------|-------------|
| **Draft** | Application created but not yet submitted |
| **Submitted** | Submitted, awaiting validation |
| **In Validation** | Under review by federation |
| **Approved** | Application accepted |
| **Rejected** | Application declined |
| **Published** | Approved and visible on calendar |
| **Returned for Correction** | Sent back to the entity for changes; can be edited and resubmitted |

### Flow

1. **Entity creates application** → Draft
2. **Entity submits** → Submitted
3. **Admin starts review** → In Validation
4. **Admin decides**:
   - Approve → Approved
   - Reject → Rejected (with reason)
5. **If approved, admin publishes** → Published (visible on calendar)

---

## Key Business Rules

### Submission Rules
- Must fill all required fields before submitting
- Cannot submit after template deadline (if template-based)
- Cannot submit if template has reached max applications
- Cannot modify after submission (except by admin)

### Validation Rules
- Only federation admins can validate
- Must provide rejection reason if rejecting
- Can add admin notes during any stage

### Publication Rules
- Only approved applications can be published
- Published applications appear on public event calendar
- Can unpublish and republish as needed

---

## Application Information

### Required Fields
- Event name
- Event type (organization/competition)
- Start and end dates
- Location (district/municipality)
- Responsible person contact

### Optional Fields
- Sport category
- Target audience description
- Expected participants
- Supporting documents

---

## Template States (Federation-Initiated)

| State | Description |
|-------|-------------|
| **Draft** | Template being prepared, not visible |
| **Open** | Accepting applications |
| **Closed** | No longer accepting (deadline passed or manual) |
| **Archived** | Historical record |

---

## Permissions

| Action | Required Permission |
|--------|---------------------|
| Create application | Entity admin |
| Submit application | Entity admin (owner) |
| View own applications | Entity member |
| Validate applications | Federation admin |
| Approve/Reject | Federation admin |
| Publish to calendar | Federation admin |
| Create templates | Federation admin |
