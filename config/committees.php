<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Committees
    |--------------------------------------------------------------------------
    |
    | The committees this installation recognizes. A committee groups the
    | licenses, certifications, membership plans, and professional roles that
    | belong to one area of the federation's activity. `CommitteeSeeder` seeds
    | this list into the `committee` table.
    |
    | Each entry:
    |   - code:                     stable identifier used throughout the app
    |   - name:                     internal/admin display name
    |   - is_international:          whether the committee's content is shared
    |                               through the international portal (drives
    |                               license/certification visibility scopes)
    |   - individual_display_name:  optional public-facing label (falls back to
    |                               `name` when omitted)
    |   - slug:                     URL/route-name base for this committee's
    |                               licenses-attributed screens (e.g. `sport`)
    |   - title_slug:               translation-key base for licenses-attributed
    |                               page titles (e.g. `sport`, `cmas_diving`)
    |   - attributed:               licenses-attributed screen wiring (see below)
    |   - purchase:                 optional license-purchase wiring (see below)
    |
    | The defaults below describe the diving example deployment. Override this
    | file per deployment to define your own committees.
    |
    | The `purchase` block makes each committee's license-purchase pages and
    | routes deployment-agnostic. For each committee you may declare an `entity`
    | and/or a `members` purchase page; the routing layer generates one named
    | route per declared page and `LicensePurchaseController::show()` renders the
    | shared purchase view for it. Each page entry:
    |   - slug:      URL segment + route name (route is `entity.{slug}.index`)
    |   - title:     translation key for the page heading (falls back to a
    |                committee-label-driven default when omitted)
    |   - subtitle:  translation key for the page subheading (same fallback)
    | A `members` entry may also set:
    |   - entity_license_via:  committee code whose entity-license purchase page a
    |                          member purchase redirects to when the entity has no
    |                          active entity license (defaults to the committee
    |                          itself).
    | A committee whose entity flow is a custom screen rather than a generated
    | purchase page (the diving services wizard, below) sets `entity_route` to that
    | route name instead of an `entity` page.
    |
    | The `attributed` block wires the licenses-attributed listing screens that
    | exist per portal (entity, federation, admin, individual). Route names and
    | page titles are derived by convention from `slug`/`title_slug`:
    |   - entity portal:      entity.{slug}-licenses-attributed.index (entity) and
    |                         entity.{slug}-member-licenses-attributed.index (members)
    |   - federation/admin:   {portal}.{slug}-{holder}-licenses-attributed.index
    |   - individual portal:  individual.{slug}-licenses-attributed.index
    | Federation/admin/individual titles use the `{portal}_{title_slug}_..._licenses_title`
    | translation keys. The entity portal's titles are irregular, so they are listed
    | explicitly under `attributed.entity_portal` (and that list also declares which
    | holder screens the entity portal exposes).
    |
    */

    'list' => [
        [
            'code' => 'SPORT',
            'name' => 'Sport Committee',
            'is_international' => false,
            'individual_display_name' => 'Underwater Sports',
            'slug' => 'sport',
            'title_slug' => 'sport',
            // Sidebar section wiring (see App\Support\CommitteeMenu). Drives the
            // committee's generated menu section/children per portal.
            'menu' => [
                'label' => 'menu.federation.sport',
                'icon' => 'flag',
                'order' => 4,
                // Entity-holder license-attributed link label (clubs for sport).
                'entities_label' => 'menu.federation.clubs',
                // Individual-holder license-attributed breakdown by professional role.
                'professionals' => [
                    'athlete' => 'menu.federation.athletes',
                    'coach' => 'menu.federation.coaches',
                    'refereejudge' => 'menu.federation.referees_judges',
                ],
            ],
            'attributed' => [
                'entity_portal' => [
                    'entity' => 'licenses.Sport Club Licenses',
                    'members' => 'licenses.Sport Licenses',
                ],
            ],
            'purchase' => [
                'entity' => [
                    'slug' => 'sport-license-purchase',
                    'title' => 'licenses.Purchase Sport Club License',
                    'subtitle' => 'licenses.Purchase a sport license for your club',
                ],
                'members' => [
                    'slug' => 'sport-member-license-purchase',
                    'title' => 'licenses.Purchase Sport Licenses',
                    'subtitle' => 'licenses.Select members and purchase sport licenses on their behalf',
                ],
                'individual' => [
                    'slug' => 'sport-license-purchase',
                    'title' => 'licenses.individual_sport_license_title',
                    'subtitle' => 'licenses.individual_sport_license_subtitle',
                ],
            ],
        ],
        [
            'code' => 'SCIENTIFIC',
            'name' => 'International Scientific Committee',
            'is_international' => true,
            'individual_display_name' => 'Environment & Science',
            'slug' => 'scientific',
            'title_slug' => 'scientific',
            // Professional role whose certifications this committee's instructors
            // issue (used by GetCertificationsFromInstructorAction).
            'instructor_role_code' => 'SCIENTIFICINSTRUCTOR',
            'menu' => [
                'label' => 'menu.federation.scientific',
                'icon' => 'chart-pie',
                'order' => 6,
                'professionals' => [
                    'instructorleader' => 'menu.federation.instructor_leaders',
                ],
            ],
            'attributed' => [
                'entity_portal' => [
                    'entity' => 'licenses.Scientific Entity Licenses',
                    'members' => 'licenses.Scientific Professional Licenses',
                ],
            ],
            'purchase' => [
                'entity' => [
                    'slug' => 'scientific-license-purchase',
                    'title' => 'licenses.Purchase Scientific Entity License',
                    'subtitle' => 'licenses.Purchase a scientific license for your entity',
                ],
                'members' => [
                    'slug' => 'scientific-member-license-purchase',
                    'title' => 'licenses.Purchase Scientific Professional Licenses',
                    'subtitle' => 'licenses.Select members and purchase scientific licenses on their behalf',
                ],
                'individual' => [
                    'slug' => 'scientific-license-purchase',
                    'title' => 'licenses.individual_scientific_license_title',
                    'subtitle' => 'licenses.individual_scientific_license_subtitle',
                ],
            ],
        ],
        [
            'code' => 'DIVING',
            'name' => 'International Diving Committee',
            'is_international' => true,
            'individual_display_name' => 'International Diving',
            'slug' => 'international-diving',
            'title_slug' => 'cmas_diving',
            // Professional role whose certifications this committee's instructors
            // issue (used by GetCertificationsFromInstructorAction).
            'instructor_role_code' => 'DIVINGINSTRUCTOR',
            'menu' => [
                'label' => 'menu.federation.diving',
                'icon' => 'globe-alt',
                'order' => 5,
                'professionals' => [
                    'instructorleader' => 'menu.federation.instructor_leaders',
                ],
            ],
            'attributed' => [
                'entity_portal' => [
                    'entity' => 'licenses.International Entity Licenses',
                    'members' => 'licenses.International Professional Licenses',
                ],
            ],
            'purchase' => [
                'entity' => [
                    'slug' => 'international-diving-license-purchase',
                    'title' => 'licenses.Purchase International Entity License',
                    'subtitle' => 'licenses.Purchase an international license for your entity',
                ],
                'members' => [
                    'slug' => 'international-diving-member-license-purchase',
                    'title' => 'licenses.Purchase International Professional Licenses',
                    'subtitle' => 'licenses.Select members and purchase international licenses on their behalf',
                ],
                'individual' => [
                    'slug' => 'international-diving-license-purchase',
                    'title' => 'licenses.individual_cmas_diving_license_title',
                    'subtitle' => 'licenses.individual_cmas_diving_license_subtitle',
                ],
            ],
        ],
        [
            'code' => 'DIVINGSERVICES',
            'name' => 'Diving Services Committee',
            'is_international' => false,
            'individual_display_name' => 'Diving Services',
            'slug' => 'national-diving',
            'title_slug' => 'national_diving',
            'attributed' => [
                'entity_portal' => [
                    // No entity licenses-attributed screen in the entity portal; members only.
                    'members' => 'licenses.Primary Diving Services Licenses',
                ],
            ],
            'purchase' => [
                // The entity diving-services flow is a dedicated wizard, not a
                // generated purchase page, so no `entity` slug is declared.
                'entity_route' => 'entity.diving_licenses.request',
                'members' => [
                    'slug' => 'national-diving-member-license-purchase',
                    'title' => 'licenses.Purchase Primary Diving Services Licenses',
                    'subtitle' => 'licenses.Select members and purchase primary diving licenses on their behalf',
                    // No entity purchase page of its own; members are gated by the
                    // international diving entity license.
                    'entity_license_via' => 'DIVING',
                ],
                'individual' => [
                    'slug' => 'national-diving-license-purchase',
                    'title' => 'licenses.individual_national_diving_license_title',
                    'subtitle' => 'licenses.individual_national_diving_license_subtitle',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Related committees
    |--------------------------------------------------------------------------
    |
    | Some committees' certifications/licenses are shown together. In the diving
    | example, a DIVING view also lists SCIENTIFIC content. Each key maps a
    | committee code to the additional committee codes to include alongside it.
    | Leave empty for no grouping.
    |
    */

    'related' => [
        'DIVING' => ['SCIENTIFIC'],
    ],

];
