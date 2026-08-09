---
title: Territorial Federation Assignment
description: Configure and operate club- and residence-based territorial federation assignment
---

# Territorial Federation Assignment

Digital Sports CRM resolves an individual's territorial federation through one shared domain
policy. The policy is country-neutral: deployments provide the administrative geography and map
territorial federations to zones, while the application applies the same precedence and review
rules everywhere.

## Resolution order

The resolver applies these rules in order:

1. Find the individual's active club relationships.
2. Find the clubs' active relationships with local federations (`is_local = true`).
3. If those relationships identify exactly one local federation, assign it with source `club`.
4. If no active club provides a territorial federation, resolve the individual's municipality or
   district to its administrative zone and find the local federation mapped to that zone.
5. If that identifies exactly one local federation, assign it with source `residence`.

An active club always takes precedence over residence. Changing the address of a club member does
not transfer the member to the federation of the new address. Removing the applicable club
relationship triggers reconciliation and can fall back to residence.

## Ambiguous and missing mappings

The resolver never selects the first database row. When zero territorial federations match, the
outcome is `unresolved`; when more than one matches, it is `ambiguous`. In both cases no new
automatic territorial membership is created, existing assignments are preserved, and
an activity entry records the outcome and candidate federation IDs for operational review.

Correct the underlying mapping and run the same reconciliation workflow again. Typical causes are:

- a state or operational zone with no local federation;
- two local federations mapped to the same state;
- an active club associated with multiple local federations;
- a legacy district without an administrative or operational zone.

## Assignment provenance

Automatic and operator-controlled origins are recorded on `individual_federation`:

| Field | Purpose |
|-------|---------|
| `assignment_source` | `club`, `residence`, `manual`, or `import` |
| `assignment_entity_id` | Club that determined the assignment, when exactly one applies |
| `assignment_zone_id` | State or operational zone used for residence resolution |
| `assignment_district_id` | Municipality or district used for residence resolution |
| `assigned_at` | Time at which the recorded decision was made |

Club assignments are active because they are derived from an already active club relationship.
New residence assignments are pending and follow the existing federation approval workflow. An
existing active residence membership is not downgraded merely because it is reconciled again.

Explicit local-federation changes through the individual administration workflow are marked
`manual` and take precedence over later automatic reconciliation. Geography-based assignments
created during bulk import are marked `import`; they retain that provenance until a later club or
residence reconciliation supersedes them.

## Federation-zone configuration

A territorial federation must:

- have `is_local = true`;
- belong to the same country as the residence district;
- be linked through `federation_zone` to the applicable administrative or operational zone.

For Brazil, map each state federation to exactly one `administrative_level_1` zone such as `BR-SP`.
Municipalities already point directly to their state through `districts.administrative_zone_id`.
Legacy installations may continue using `district_zone`; the resolver uses that relationship when
no direct administrative parent exists.

## Existing installations

The migration does not infer provenance for historical `individual_federation` rows. Existing rows
therefore keep `assignment_source = null` until an explicit reconciliation or operator decision can
establish their origin safely. This avoids relabelling membership history based on incomplete data.

Before enabling automatic territorial assignment in an existing deployment:

1. verify local federations and their country values;
2. ensure each applicable zone maps to at most one local federation;
3. verify active club-to-federation relationships;
4. review legacy districts without a zone;
5. test representative club, residence, and ambiguous cases in a staging copy.
