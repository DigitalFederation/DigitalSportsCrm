<?php

/*
|--------------------------------------------------------------------------
| Legacy config-based menu
|--------------------------------------------------------------------------
|
| This file is the fallback menu used only when the dynamic (database) menu is
| disabled (see config/features.php `dynamic_menu`). The per-committee entries
| below (Sport/Diving/Scientific sub-menus and their children) describe the
| diving example deployment's navigation; a deployment with a different
| committee set provides its own menu, the same way it provides its own
| committees, licenses, and roles. The menu *system* itself is committee-driven
| (menu items carry a committee_id and filter by committee code at render time).
| The per-committee menu items are generated from config/committees.php via
| App\Support\CommitteeMenu; MenuSeeder resolves committee codes to ids at seed
| time when persisting the database menu.
|
*/

$menu = [

    'admin' => [
        [
            'name' => 'menu.admin.dashboard',
            'icon' => 'chart-bar',
            'route' => ['admin.dashboard'],
            'active' => ['dashboard'],
        ],
        [
            'name' => 'menu.admin.federation',
            'icon' => 'building-office',
            'route' => '',
            'can' => 'access federations',
            'active' => ['federation', 'federations'],
            'children' => [
                [
                    'name' => 'menu.admin.national_federations',
                    'route' => ['admin.federation.index'],
                    'active' => ['federations'],
                ],
                [
                    'name' => 'menu.admin.local_organizations',
                    'route' => ['admin.federation.index', ['filter[filter_is_local]' => true]],
                    'active' => ['federation'],
                ],
            ],
        ],
        [
            'name' => 'menu.admin.memberships',
            'icon' => 'document-plus',
            'route' => '',
            'can' => 'access memberships',
            'active' => ['memberships', 'membership-plans', 'membership', 'membership-plan'],
            'children' => [
                [
                    'name' => 'menu.admin.membership_packages',
                    'route' => ['admin.membership-packages.index'],
                    'active' => ['membership-packages'],
                ],
                [
                    'name' => 'menu.admin.affiliation_plans',
                    'route' => ['admin.affiliation-plans.index'],
                    'active' => ['affiliation-plans'],
                ],
                [
                    'name' => 'menu.admin.insurance_plans',
                    'route' => ['admin.insurance-plans.index'],
                    'active' => ['insurance-plans'],
                ],
                [
                    'name' => 'menu.admin.insurances',
                    'route' => ['admin.insurances.index'],
                    'active' => ['insurances'],
                ],
                [
                    'name' => 'menu.admin.member_subscriptions',
                    'route' => ['admin.member-subscriptions.index'],
                    'active' => ['member-subscriptions'],
                ],
                [
                    'name' => 'menu.admin.affiliations',
                    'route' => ['admin.affiliations.index'],
                    'active' => ['affiliations'],
                ],

            ],
        ],
        [
            'name' => 'menu.admin.entities',
            'icon' => 'building-office-2',
            'route' => ['admin.entity.index'],
            'can' => 'access entities',
            'active' => ['entity', 'entities'],
        ],
        [
            'name' => 'menu.admin.individuals',
            'icon' => 'users',
            'route' => ['admin.individual.index'],
            'can' => 'access individuals',
            'active' => ['individual', 'individuals'],
        ],
        [
            'name' => 'menu.admin.events',
            'icon' => 'ticket',
            'route' => '',
            'can' => 'access events',
            'active' => ['evt-events', 'events'],
            'children' => [
                [
                    'name' => 'menu.admin.events_list',
                    'route' => ['admin.evt-events.events.index'],
                    'active' => ['evt-events', 'events'],
                ],
                [
                    'name' => 'menu.admin.event_attributes',
                    'route' => ['admin.evt-events.attributes.index'],
                    'active' => ['attributes'],
                ],
            ],
        ],
        [
            'name' => 'menu.admin.certifications',
            'icon' => 'credit-card',
            'route' => '',
            'can' => 'access certifications',
            'active' => [
                'certification',
                'certifications',
                'certification-attributed',
            ],
            'children' => [
                [
                    'name' => 'menu.admin.certifications_manager',
                    'route' => ['admin.certification.index'],
                    'active' => ['certifications', 'certification'],
                ],
                // Per-committee attributed-certification links generated from config.
                ...\App\Support\CommitteeMenu::committeeChildren('admin.certification-attributed.index', [], ['certification-attributed']),
            ],
        ],
        [
            'name' => 'menu.admin.licenses',
            'icon' => 'document-text',
            'route' => '',
            'can' => 'access licenses',
            'active' => [
                'license',
                'licenses',
                'license-attributed',
                'licenses-attributed',
            ],
            'children' => [
                [
                    'name' => 'menu.admin.license_manager',
                    'can' => 'access licenses manager',
                    'route' => ['admin.license.index'],
                    'active' => ['licenses', 'license'],
                ],
                // Per-committee attributed-license links generated from config
                // (the entity/individual holder split is available via the
                // listing's own filters).
                ...\App\Support\CommitteeMenu::committeeChildren('admin.license-attributed.index', [], ['licenses-attributed', 'license-attributed']),
            ],
        ],
        [
            'name' => 'menu.admin.diving_services',
            'icon' => 'globe-alt',
            'route' => '',
            'can' => 'access diving certifications attributed',
            'active' => ['entity-diving-license-validation', 'individual-diving-license-validation', 'diving-professional-certifications', 'diving-professionals'],
            'children' => [
                [
                    'name' => 'menu.admin.entity_diving_license_validation',
                    'route' => ['admin.entity_diving_license_validation.index'],
                    'can' => 'access licenses',
                    'active' => ['entity-diving-license-validation'],
                ],
                [
                    'name' => 'menu.admin.individual_diving_license_validation',
                    'route' => ['admin.individual_diving_license_validation.index'],
                    'can' => 'access licenses',
                    'active' => ['individual-diving-license-validation'],
                ],
                [
                    'name' => 'menu.admin.diving_professional_certifications',
                    'route' => ['admin.diving_professional_certifications.index'],
                    'can' => 'access diving certifications attributed',
                    'active' => ['diving-professional-certifications'],
                ],
                [
                    'name' => 'menu.admin.diving_professionals_list',
                    'route' => ['admin.diving_professionals.index'],
                    'can' => 'access individuals',
                    'active' => ['diving-professionals'],
                ],
            ],
        ],
        [
            'name' => 'menu.admin.users',
            'icon' => 'user-circle',
            'route' => '',
            'can' => 'access users',
            'active' => ['users', 'roles'],
            'children' => [
                [
                    'name' => 'menu.admin.latest_users',
                    'route' => ['admin.users.index'],
                    'active' => ['licenses', 'license'],
                ],
                [
                    'name' => 'menu.admin.role_management',
                    'route' => ['admin.role-management.index'],
                    'active' => ['role-management'],
                    'can' => 'access role management dashboard',
                ],
                [
                    'name' => 'menu.admin.permission_management',
                    'route' => ['admin.permission-management.index'],
                    'active' => ['permission-management'],
                    'can' => ['manage-permissions', 'view-permissions'],
                ],
                [
                    'name' => 'menu.admin.route_permissions',
                    'route' => ['admin.route-permissions.index'],
                    'active' => ['route-permissions'],
                    'can' => 'manage-route-permissions',
                ],
                [
                    'name' => 'menu.admin.role_mappings',
                    'route' => ['admin.role-mappings.index'],
                    'active' => ['role-mappings'],
                ],
                [
                    'name' => 'menu.dynamic.admin.menu_management',
                    'route' => ['admin.menu-management.index'],
                    'active' => ['menu-management'],
                    'can' => 'access users',
                ],
            ],
        ],
        [
            'name' => 'menu.admin.payments',
            'icon' => 'document',
            'route' => '',
            'can' => 'access documents',
            'active' => ['documents'],
            'children' => [
                [
                    'name' => 'menu.admin.documents',
                    'route' => ['admin.document.index'],
                    'active' => ['documents'],
                ],
                [
                    'name' => 'menu.admin.invoices',
                    'route' => ['admin.document.invoices'],
                    'active' => ['documents'],
                ],
            ],
        ],
        [
            'name' => 'menu.admin.files_area',
            'icon' => 'document-arrow-down',
            'route' => '',
            'can' => 'access attachments menu',
            'active' => ['attachments'],
            'children' => [
                [
                    'name' => 'menu.admin.administrative',
                    'route' => ['admin.attachments.index'],
                    'active' => ['attachments'],
                ],
                // Per-committee attachment links generated from config/committees.php.
                ...\App\Support\CommitteeMenu::attachmentChildren('admin'),
            ],
        ],
        [
            'name' => 'menu.admin.legal_documents',
            'icon' => 'document',
            'route' => '',
            'can' => 'access official documents',
            'active' => ['official-documents'],
            'children' => [
                [
                    'name' => 'menu.admin.national_federations',
                    'route' => ['admin.official-documents.index', 'federation'],
                    'active' => ['official-documents'],
                ],
                [
                    'name' => 'menu.admin.individuals',
                    'route' => ['admin.official-documents.index', 'individual'],
                    'active' => ['official-documents'],
                ],
                [
                    'name' => 'menu.admin.entities',
                    'route' => ['admin.official-documents.index', 'entity'],
                    'active' => ['official-documents'],
                ],
            ],
        ],
        [
            'name' => 'menu.admin.settings',
            'icon' => 'chart-bar',
            'route' => '',
            'can' => 'access settings',
            'active' => ['districts', 'zones', 'member-number-settings', 'professional-roles', 'backups', 'homepage-settings'],
            'children' => [
                [
                    'name' => 'menu.admin.home_page',
                    'route' => ['admin.homepage-settings.index'],
                    'active' => ['homepage-settings'],
                ],
                [
                    'name' => 'menu.admin.reports',
                    'route' => ['admin.reports.index'],
                    'active' => ['reports'],
                ],
                [
                    'name' => 'menu.admin.statistics',
                    'route' => ['admin.reports.stats'],
                    'active' => ['reports'],
                ],
                [
                    'name' => 'menu.admin.staff_roles',
                    'route' => ['admin.staff-roles.index'],
                    'active' => ['staff-roles'],
                ],
                [
                    'name' => 'menu.admin.member_number_settings',
                    'route' => ['admin.member-number-settings.index'],
                    'active' => ['member-number-settings'],
                ],
                [
                    'name' => 'menu.admin.professional_roles',
                    'route' => ['admin.professional-roles.index'],
                    'active' => ['professional-roles'],
                ],
                [
                    'name' => 'menu.admin.districts',
                    'route' => ['admin.districts.index'],
                    'active' => ['districts'],
                ],
                [
                    'name' => 'menu.admin.zones',
                    'route' => ['admin.zones.index'],
                    'active' => ['zones'],
                ],
                [
                    'name' => 'menu.admin.operations_center',
                    'route' => ['admin.operations.index'],
                    'active' => ['operations'],
                ],
                [
                    'name' => 'menu.admin.database_backups',
                    'route' => ['admin.backups.index'],
                    'can' => 'access backups',
                    'active' => ['backups'],
                ],
            ],
        ],
    ],

    'federation' => [
        [
            'name' => 'menu.federation.dashboard',
            'icon' => 'chart-bar',
            'route' => ['federation.dashboard'],
            'active' => ['dashboard'],
        ],
        [
            'name' => 'menu.federation.memberships',
            'icon' => 'document-plus',
            'route' => ['federation.membership.index'],
            'can' => 'access memberships',
            'active' => ['membership'],
        ],
        [
            'name' => 'menu.federation.members',
            'icon' => 'user-group',
            'route' => '',
            'can' => ['access individuals', 'access entities'],
            'active' => ['individuals', 'individual', 'entities', 'entity'],
            'children' => [
                [
                    'name' => 'menu.federation.individuals',
                    'route' => ['federation.individual.index'],
                    'active' => ['individuals', 'individual'],
                ],
                [
                    'name' => 'menu.federation.entities',
                    'route' => ['federation.entity.index'],
                    'active' => ['entities', 'entity'],
                ],
            ],
        ],
        // Per-committee sections generated from config/committees.php (see
        // App\Support\CommitteeMenu). Add/remove a committee there to add/remove
        // its sidebar section.
        ...\App\Support\CommitteeMenu::sections('federation'),
        [
            'name' => 'menu.federation.events',
            'icon' => 'ticket',
            'route' => '',
            'can' => 'access events',
            'active' => ['evt-events'],
            'children' => [
                [
                    'name' => 'menu.federation.registration',
                    'route' => ['federation.evt-events.events.index'],
                    'active' => ['evt-events.event'],
                ],
                [
                    'name' => 'menu.federation.create_event',
                    'route' => ['federation.evt-events.events.create'],
                    'can' => 'manage-events',
                    'active' => ['evt-events.events.create'],
                ],
                [
                    'name' => 'menu.federation.export_events',
                    'route' => ['federation.evt-events.events.export'],
                    'can' => 'manage-events',
                    'active' => ['evt-events.events.export'],
                ],
            ],

        ],
        [
            'name' => 'menu.federation.files_area',
            'icon' => 'document-arrow-down',
            'route' => '',
            'active' => ['attachments'],
            'children' => [
                [
                    'name' => 'menu.federation.administrative',
                    'route' => ['federation.attachments.index'],
                    'active' => ['attachments'],
                ],
                // Per-committee attachment links generated from config/committees.php.
                ...\App\Support\CommitteeMenu::attachmentChildren('federation'),
            ],
        ],
        [
            'name' => 'menu.federation.payments',
            'icon' => 'currency-dollar',
            'route' => ['federation.document.index'],
            'can' => 'access documents',
            'active' => ['documents'],
        ],
    ],

    'entity' => [
        [
            'name' => 'menu.entity.dashboard',
            'icon' => 'chart-bar',
            'route' => ['entity.dashboard'],
            'active' => ['dashboard'],
        ],
        [
            'name' => 'menu.entity.entity_plans',
            'icon' => 'building-office',
            'route' => '',
            'can' => 'access memberships',
            'active' => ['memberships', 'membership-plans', 'membership', 'membership-plan'],
            'children' => [
                [
                    'name' => 'menu.entity.insurance_plans',
                    'route' => ['entity.insurances.index'],
                    'active' => ['insurances'],
                ],
                [
                    'name' => 'menu.entity.affiliation_plans',
                    'route' => ['entity.subscriptions.index'],
                    'active' => ['subscriptions'],
                ],
            ],
        ],
        [
            'name' => 'menu.entity.members_plans',
            'icon' => 'building-office',
            'route' => '',
            'can' => 'access memberships',
            'active' => ['memberships', 'membership-plans', 'membership', 'membership-plan'],
            'children' => [
                [
                    'name' => 'menu.entity.insurance_plans',
                    'route' => ['entity.individual-insurances.index'],
                    'active' => ['insurances'],
                ],
                [
                    'name' => 'menu.entity.affiliation_plans',
                    'route' => ['entity.individual-memberships.index'],
                    'active' => ['subscriptions'],
                ],
            ],
        ],
        [
            'name' => 'menu.entity.members',
            'icon' => 'user-group',
            'route' => ['entity.individual.index'],
            'can' => ['access individuals', 'access entities'],
            'active' => ['individuals', 'individual'],
        ],
        [
            'name' => 'menu.entity.federation_organizations',
            'icon' => 'building-office',
            'route' => ['entity.federation.index'],
            'active' => ['federation', 'federations'],
        ],
        // Per-committee sections generated from config/committees.php.
        ...\App\Support\CommitteeMenu::sections('entity'),
        [
            'name' => 'menu.entity.diving_services',
            'icon' => 'globe-alt',
            'route' => '',
            'can' => 'access individuals',
            'active' => ['diving-licenses', 'diving-instructors'],
            'children' => [
                [
                    'name' => 'menu.entity.service_provider_licenses',
                    'route' => ['entity.diving_licenses.index'],
                    'active' => ['diving-licenses'],
                ],
                [
                    'name' => 'menu.entity.diving_professionals',
                    'route' => ['entity.diving_professionals.index'],
                    'active' => ['diving-professionals'],
                ],
            ],
        ],
        [
            'name' => 'menu.entity.licenses',
            'icon' => 'shopping-cart',
            'route' => '',
            'can' => 'access licenses',
            'active' => ['sport-license-purchase', 'international-diving-license-purchase', 'scientific-license-purchase', 'sport-member-license-purchase', 'international-diving-member-license-purchase', 'scientific-member-license-purchase', 'national-diving-member-license-purchase'],
            'children' => [
                [
                    'name' => 'menu.entity.purchase_entity_license',
                    'route' => 'entity.sport-license-purchase.index',
                    'active' => ['sport-license-purchase', 'international-diving-license-purchase', 'scientific-license-purchase'],
                ],
                [
                    'name' => 'menu.entity.purchase_member_licenses',
                    'route' => 'entity.sport-member-license-purchase.index',
                    'active' => ['sport-member-license-purchase', 'international-diving-member-license-purchase', 'scientific-member-license-purchase', 'national-diving-member-license-purchase'],
                ],
            ],
        ],
        [
            'name' => 'menu.entity.events',
            'icon' => 'ticket',
            'route' => '',
            'can' => 'access events',
            'active' => ['evt-events'],
            'children' => [
                [
                    'name' => 'menu.entity.registration',
                    'route' => ['entity.evt-events.events.index'],
                    'active' => ['evt-events.event'],
                ],
            ],
        ],
        [
            'name' => 'menu.entity.files_area',
            'icon' => 'document-arrow-down',
            'route' => '',
            'active' => ['attachments'],
            'children' => [
                [
                    'name' => 'menu.entity.administrative',
                    'route' => ['entity.attachments.index'],
                    'active' => ['attachments'],
                ],
                // Per-committee attachment links generated from config/committees.php.
                ...\App\Support\CommitteeMenu::attachmentChildren('entity'),
            ],
        ],
        [
            'name' => 'menu.entity.official_documents',
            'icon' => 'document',
            'route' => ['entity.official-documents.index'],
            'active' => ['official-documents'],
        ],
        [
            'name' => 'menu.entity.payments',
            'icon' => 'currency-dollar',
            'route' => ['entity.document.index'],
            'can' => 'access orders',
            'active' => ['documents'],
        ],
    ],

    'individual' => [
        [
            'name' => 'menu.individual.dashboard',
            'icon' => 'chart-bar',
            'route' => ['individual.dashboard'],
            'active' => ['dashboard'],
        ],
        [
            'name' => 'menu.individual.federation_organization',
            'icon' => 'building-office',
            'route' => ['individual.federation.index'],
            'active' => ['federation'],
        ],
        [
            'name' => 'menu.individual.entities',
            'icon' => 'home',
            'route' => ['individual.entity.index'],
            'active' => ['entity'],
        ],
        [
            'name' => 'menu.individual.affiliations',
            'icon' => 'wallet',
            'route' => ['individual.subscriptions.index'],
            'active' => ['subscriptions'],
        ],
        [
            'name' => 'menu.individual.insurances',
            'icon' => 'identification',
            'route' => ['individual.insurance.index'],
            'active' => ['insurance'],
        ],
        [
            'name' => 'menu.individual.diving_professional',
            'icon' => 'globe-alt',
            'route' => '',
            'active' => ['diving-certifications', 'official-documents', 'diving-entities'],
            'children' => [
                [
                    'name' => 'menu.individual.diving_certifications',
                    'route' => ['individual.diving_certifications.index'],
                    'active' => ['diving-certifications'],
                ],
                [
                    'name' => 'menu.individual.diving_official_documents',
                    'route' => ['individual.official-documents.index', 'diving-professional'],
                    'active' => ['official-documents'],
                ],
                [
                    'name' => 'menu.individual.diving_entities',
                    'route' => ['individual.diving_entities.index'],
                    'active' => ['diving-entities'],
                ],
                [
                    'name' => 'menu.individual.technical_director_positions',
                    'route' => ['individual.technical_director_positions.index'],
                    'active' => ['technical-director-positions'],
                ],
            ],
        ],
        [
            'name' => 'menu.individual.my_certifications',
            'icon' => 'credit-card',
            'can' => ['access diver menu', 'access scientific menu', 'access sport menu'],
            'route' => ['individual.certification-card.index'],
            'active' => ['certification-card', 'certification-attributed'],
            'children_backup' => [
                [
                    'name' => 'menu.individual.cards',
                    'route' => ['individual.certification-card.index'],
                    'active' => ['certification-card'],
                ],
                ...\App\Support\CommitteeMenu::committeeChildren('individual.certification-attributed.index', [], ['certification-attributed']),
            ],
        ],
        [
            'name' => 'menu.individual.my_licenses',
            'icon' => 'document-text',
            'can' => [],
            'route' => '',
            'active' => ['licenses-attributed', 'license-attributed'],
            // Per-committee license links generated from config/committees.php.
            'children' => \App\Support\CommitteeMenu::committeeChildren('individual.license-attributed.index', [], ['licenses-attributed']),
        ],
        [
            'name' => 'menu.individual.personal_documents',
            'icon' => 'document',
            'route' => '',
            'can' => [],
            'active' => ['official-documents'],
            'children' => [
                [
                    'name' => 'menu.individual.diver',
                    'route' => ['individual.official-documents.index', 'diver'],
                    'can' => ['access diver official documents', 'access diving official documents'],
                    'active' => ['official-documents'],
                ],
                [
                    'name' => 'menu.individual.instructor_leader',
                    'route' => ['individual.official-documents.index', 'instructor-leader'],
                    'can' => ['access diver menu', 'access scientific menu'],
                    'active' => ['official-documents'],
                ],
                [
                    'name' => 'menu.individual.coach',
                    'route' => ['individual.official-documents.index', 'coach'],
                    'can' => ['access coach menu'],
                    'active' => ['official-documents'],
                ],
                [
                    'name' => 'menu.individual.referee_judge',
                    'route' => ['individual.official-documents.index', 'referee-judge'],
                    'can' => ['access referee menu', 'access judge menu'],
                    'active' => ['official-documents'],
                ],
                [
                    'name' => 'menu.individual.athlete',
                    'route' => ['individual.official-documents.index', 'athlete'],
                    'can' => [],
                    'active' => ['official-documents'],
                ],
            ],
        ],
        [
            'name' => 'menu.individual.athlete',
            'icon' => 'user',
            'route' => '',
            'can' => [],
            'active' => ['athlete', 'official-documents'],
            'children' => [
                [
                    'name' => 'menu.individual.clubs',
                    'route' => ['individual.athlete.index', ['filter[status]' => 'active']],
                    'active' => ['athlete'],
                ],
                [
                    'name' => 'menu.individual.club_requests',
                    'route' => ['individual.athlete.index'],
                    'active' => ['athlete'],
                ],
            ],
        ], // Athlete - No permission required so individuals can see pending invitations
        [
            'name' => 'menu.individual.coach',
            'icon' => 'identification',
            'route' => '',
            'can' => ['access coach menu'],
            'active' => ['coach', 'certification-attributed', 'official-documents'],
            'children' => [
                [
                    'name' => 'menu.individual.clubs',
                    'route' => ['individual.coach.index', ['filter[status]' => 'active']],
                    'active' => ['athlete'],
                ],
                [
                    'name' => 'menu.individual.clubs_requests',
                    'route' => ['individual.coach.index'],
                    'active' => ['coach'],
                ],
            ],
        ], // Coach
        [
            'name' => 'menu.individual.instructor_leader',
            'icon' => 'academic-cap',
            'route' => '',
            'can' => ['access instructor menu'],
            'active' => ['certification-validate', 'instructor', 'official-documents'],
            'children' => [
                [
                    'name' => 'menu.individual.certifications_to_approve',
                    'route' => ['individual.certification-validate.index'],
                    'active' => ['certification-validate'],
                ],
                [
                    'name' => 'menu.individual.issued_certifications',
                    'route' => ['individual.certification-validate.index', ['filter[filter_status]' => 'active']],
                    'active' => ['certification-validate'],
                ],
                [
                    'name' => 'menu.individual.diving_entities',
                    'route' => ['individual.instructor.index', 'diving'],
                    'active' => ['instructor'],
                ],
                [
                    'name' => 'menu.individual.scientific_entities',
                    'route' => ['individual.instructor.index', 'scientific'],
                    'active' => ['instructor'],
                ],
            ],
        ], // Instructor
        [
            'name' => 'menu.individual.referee_judge',
            'icon' => 'briefcase',
            'route' => '',
            'can' => ['access referee menu'],
            'active' => ['official-documents'],
            'children' => [],
        ],
        [
            'name' => 'menu.individual.events',
            'icon' => 'ticket',
            'route' => ['individual.evt-events.events.index'],
            'can' => [],
            'active' => ['evt-events'],
        ],
        [
            'name' => 'menu.individual.files_area',
            'icon' => 'document-arrow-down',
            'route' => '',
            'can' => [],
            'active' => ['attachments'],
            'children' => [
                [
                    'name' => 'menu.individual.administrative',
                    'route' => ['individual.attachments.index'],
                    'can' => [],
                    'active' => ['attachments'],
                ],
                // Per-committee attachment links generated from config/committees.php.
                ...\App\Support\CommitteeMenu::attachmentChildren('individual'),
            ],
        ],
        [
            'name' => 'menu.individual.payments',
            'icon' => 'currency-dollar',
            'route' => ['individual.document.index'],
            'can' => [],
            'active' => ['documents'],
        ],

    ],
];

return $menu;
