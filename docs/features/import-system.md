---
title: Import System
description: Bulk individual import from CSV/XLS files
---

# Import System

## Overview

The Import System allows federation administrators to perform bulk imports of individuals from CSV or XLS files.

---

## Key Features

### File Support
- CSV, XLS, and XLSX formats
- Maximum file size: 10MB
- UTF-8 encoding support

### Field Mapping
- Flexible column mapping UI
- Map source columns to platform fields
- Save mappings for reuse

### Validation & Preview
- Validates all data before import
- Shows count of valid records
- Lists warnings (potential duplicates)
- Lists errors (invalid data)
- Downloadable error report

### Duplicate Handling
- Detects duplicates by name + surname + birthdate + country
- Resolution strategies: Skip, Update existing, or Create with suffix (`skip` / `update` / `create_with_suffix`; default `skip`)

### Processing
- Large files processed asynchronously
- Real-time progress tracking
- Background queue processing

---

## Import Workflow

1. **Upload File** - Select CSV/XLS file
2. **Map Fields** - Map columns to platform fields
3. **Preview** - Review validation results
4. **Resolve Duplicates** - Choose how to handle duplicates
5. **Import** - Process the import
6. **Review Results** - See success/failure counts

---

## Required Fields

| Field | Description |
|-------|-------------|
| Name | First name |
| Surname | Last name |
| Email | Valid email address |
| Birthdate | Date of birth |

> Country is **not** a required field. If it is not mapped/provided, the country is auto-set from the Main (default) Federation's country and a warning is recorded.

## Optional Fields

These fields are imported only if mapped to a source column:

| Field | Description |
|-------|-------------|
| Country | Country name (resolved to `country_id`); auto-set from Main Federation if omitted |
| Phone | Phone number |
| Address | Street address |
| City | City name |
| Postal Code | ZIP/postal code |
| Gender | male / female / other |

---

## Error Handling

### Validation Errors
- Missing required fields
- Invalid email format
- Invalid date format

### Warnings
- Potential duplicate records
- Fields exceeding length limits
- Country not found (the Main Federation country is used instead)

### Recovery
- Failed imports can be retried
- Error report shows which rows failed
- Fix errors in source file and re-import
