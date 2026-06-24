# Role Mapping Seeder

This seeder populates the new role mapping pivot tables (`license_roles`, `certification_roles`, and `federation_roles`) based on the current system's professional role mappings.

## Prerequisites

Before running this seeder, ensure that:
1. The role refactor migrations have been run (creating the pivot tables)
2. The existing professional roles, licenses, and certifications data is populated

## What it does

1. **Federation Roles**
   - Creates the `individual-approved` role if it doesn't exist
   - Adds an entry in `federation_roles` with NULL federation_id

2. **License Roles**
   - Maps each license to its corresponding individual role based on the professional role
   - Uses the role name mappings (e.g., 'instructor' → 'individual-instructor')
   - Creates the role if it doesn't exist

3. **Certification Roles**
   - Maps each certification to its corresponding individual role
   - Additionally creates committee-specific view roles (e.g., DIVING instructor → 'view-individual-diving-instructor')
   - Handles both primary roles and view roles

## Usage

### Run as part of all seeders
```bash
php artisan db:seed
```

### Run only this seeder
```bash
php artisan db:seed --class=RoleMappingSeeder
```

### Using the custom command
```bash
# Run the seeder
php artisan seed:role-mappings

# Drop existing mappings and re-seed (careful!)
php artisan seed:role-mappings --fresh
```

## Role Name Mappings

The seeder uses the following mappings to convert professional role slugs to individual role names:

- `instructor` → `individual-instructor`
- `coach` → `individual-coach`
- `athlete` → `individual-athlete`
- `technical_official` → `individual-technical-official`
- `leader` → `individual-leader`
- `diver` → `individual-diver`
- `diving-instructor` → `individual-diving-instructor`
- `instructor-trainer` → `individual-instructor-trainer`
- `coach-trainer` → `individual-coach-trainer`

## Committee-Specific View Roles

For certain committees, additional view roles are created:

### DIVING Committee
- `instructor` → `view-individual-diving-instructor`
- `instructor-trainer` → `view-individual-diving-instructor-trainer`

### COACHING Committee
- `coach` → `view-individual-coach`
- `coach-trainer` → `view-individual-coach-trainer`

### SPORT Committee
- `technical_official` → `view-individual-technical-official`

## Safety Features

- Uses database transactions (all-or-nothing)
- Checks for required tables before running
- Uses `insertOrIgnore` to prevent duplicate entries (idempotent)
- Provides progress bars for better feedback
- Logs all operations

## Troubleshooting

If the seeder fails:

1. Check that all required tables exist:
   ```sql
   SHOW TABLES LIKE '%_roles';
   ```

2. Ensure professional roles are properly set on licenses and certifications:
   ```sql
   SELECT COUNT(*) FROM license WHERE professional_role_id IS NULL;
   SELECT COUNT(*) FROM certification WHERE professional_role_id IS NULL;
   ```

3. Check the Laravel logs for detailed error messages:
   ```bash
   tail -f storage/logs/laravel.log
   ```