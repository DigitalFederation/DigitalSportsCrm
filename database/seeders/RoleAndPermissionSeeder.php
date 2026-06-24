<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        Permission::firstOrCreate(['name' => 'access documents']);
        Permission::firstOrCreate(['name' => 'create payment documents']);
        Permission::firstOrCreate(['name' => 'access official documents']);
        Permission::firstOrCreate(['name' => 'access federation official documents']);
        Permission::firstOrCreate(['name' => 'download individual official documents']);
        Permission::firstOrCreate(['name' => 'access documents invoices']);
        Permission::firstOrCreate(['name' => 'download reports']);

        Permission::firstOrCreate(['name' => 'access memberships']);
        Permission::firstOrCreate(['name' => 'access official documents']);

        Permission::firstOrCreate(['name' => 'access users']);
        Permission::firstOrCreate(['name' => 'manage user roles']);
        Permission::firstOrCreate(['name' => 'access certifications']);

        Permission::firstOrCreate(['name' => 'access sport certifications']);
        Permission::firstOrCreate(['name' => 'access diving and scientific certifications']);
        Permission::firstOrCreate(['name' => 'access diving and scientific certifications manager']);

        Permission::firstOrCreate(['name' => 'access sport certifications manager']);
        Permission::firstOrCreate(['name' => 'access sport certifications attributed']);

        Permission::firstOrCreate(['name' => 'access scientific certifications attributed']);
        Permission::firstOrCreate(['name' => 'access diving certifications attributed']);

        Permission::firstOrCreate(['name' => 'access licenses']);
        Permission::firstOrCreate(['name' => 'access licenses manager']);

        Permission::firstOrCreate(['name' => 'access sport individual licenses attributed']);
        Permission::firstOrCreate(['name' => 'access diving individual licenses attributed']);
        Permission::firstOrCreate(['name' => 'access scientific individual licenses attributed']);

        Permission::firstOrCreate(['name' => 'access sport entity licenses attributed']);
        Permission::firstOrCreate(['name' => 'access diving entity licenses attributed']);
        Permission::firstOrCreate(['name' => 'access scientific entity licenses attributed']);

        Permission::firstOrCreate(['name' => 'access international licenses']);
        Permission::firstOrCreate(['name' => 'access international certifications']);

        Permission::firstOrCreate(['name' => 'access federations']);
        Permission::firstOrCreate(['name' => 'access entities']);
        Permission::firstOrCreate(['name' => 'create entities']);
        Permission::firstOrCreate(['name' => 'access individuals']);

        Permission::firstOrCreate(['name' => 'access sport menu']);
        Permission::firstOrCreate(['name' => 'access sport license menu']);

        Permission::firstOrCreate(['name' => 'access diving menu']);
        Permission::firstOrCreate(['name' => 'access scientific menu']);

        Permission::firstOrCreate(['name' => 'access sport certification attributed']);
        Permission::firstOrCreate(['name' => 'access diving certification attributed']);
        Permission::firstOrCreate(['name' => 'access scientific certification attributed']);

        Permission::firstOrCreate(['name' => 'access sport entities']);
        Permission::firstOrCreate(['name' => 'access diving entities']);
        Permission::firstOrCreate(['name' => 'access scientific entities']);

        Permission::firstOrCreate(['name' => 'access athletes']);
        Permission::firstOrCreate(['name' => 'access coaches']);
        Permission::firstOrCreate(['name' => 'access referees and judges']);

        Permission::firstOrCreate(['name' => 'access freediving']);

        Permission::firstOrCreate(['name' => 'access diving individuals']);
        Permission::firstOrCreate(['name' => 'access scientific individuals']);

        Permission::firstOrCreate(['name' => 'access diving instructors and leaders']);
        Permission::firstOrCreate(['name' => 'access scientific instructors and leaders']);

        Permission::firstOrCreate(['name' => 'access sport official documents']);
        Permission::firstOrCreate(['name' => 'access diving official documents']);
        Permission::firstOrCreate(['name' => 'access scientific official documents']);

        Permission::firstOrCreate(['name' => 'access instructor-leader official documents']);
        Permission::firstOrCreate(['name' => 'access diver official documents']);
        Permission::firstOrCreate(['name' => 'access athlete official documents']);
        Permission::firstOrCreate(['name' => 'access coach official documents']);
        Permission::firstOrCreate(['name' => 'access referee-judge official documents']);

        Permission::firstOrCreate(['name' => 'access products']);

        Permission::firstOrCreate(['name' => 'access events']);
        Permission::firstOrCreate(['name' => 'manage-events']);

        Permission::firstOrCreate(['name' => 'access certification slots']);
        Permission::firstOrCreate(['name' => 'access certification slots manager']);

        Permission::firstOrCreate(['name' => 'access attachments menu']);
        Permission::firstOrCreate(['name' => 'access sport attachments']);
        Permission::firstOrCreate(['name' => 'access diving attachments']);
        Permission::firstOrCreate(['name' => 'access scientific attachments']);


        Permission::firstOrCreate(['name' => 'access coach menu']);
        Permission::firstOrCreate(['name' => 'access instructor menu']);
        Permission::firstOrCreate(['name' => 'access athlete menu']);
        Permission::firstOrCreate(['name' => 'access judge menu']);
        Permission::firstOrCreate(['name' => 'access leader menu']);
        Permission::firstOrCreate(['name' => 'access referee menu']);
        Permission::firstOrCreate(['name' => 'access diver menu']);

        Permission::firstOrCreate(['name' => 'access settings']);
        Permission::firstOrCreate(['name' => 'access orders']);
        Permission::firstOrCreate(['name' => 'access starter menu']);

        Permission::firstOrCreate(['name' => 'access files area menu']);
        Permission::firstOrCreate(['name' => 'access email notifications']);

        // Impersonate permissions
        Permission::firstOrCreate(['name' => 'impersonate users']);

        // LMS permissions
        Permission::firstOrCreate(['name' => 'access lms menu']);
        Permission::firstOrCreate(['name' => 'access lms students']);
        Permission::firstOrCreate(['name' => 'access lms progression']);
        Permission::firstOrCreate(['name' => 'manage lms progression']);

        Permission::firstOrCreate(['name' => 'manage_menus']);

        // New simplified role system
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $association_sport_admin = Role::firstOrCreate(['name' => 'association-sport-admin']);
        $association_scientific_admin = Role::firstOrCreate(['name' => 'association-scientific-admin']);
        $association_admin = Role::firstOrCreate(['name' => 'association-admin']);
        $association_territorial_admin = Role::firstOrCreate(['name' => 'association-territorial-admin']);
        $federation_admin = Role::firstOrCreate(['name' => 'federation-admin']);

        // Entity roles
        $entity_admin = Role::firstOrCreate(['name' => 'entity-admin']);
        $entity_sport = Role::firstOrCreate(['name' => 'entity-sport']);
        $entity_diving_services = Role::firstOrCreate(['name' => 'entity-diving-services']);
        $entity_international = Role::firstOrCreate(['name' => 'entity-international']);

        $individual_approved = Role::firstOrCreate(['name' => 'individual-approved']);
        $individual_coach = Role::firstOrCreate(['name' => 'individual-coach']);
        $individual_instructor = Role::firstOrCreate(['name' => 'individual-instructor']);
        $individual_athlete = Role::firstOrCreate(['name' => 'individual-athlete']);
        $individual_technical_official = Role::firstOrCreate(['name' => 'individual-technical-official']);
        $individual_leader = Role::firstOrCreate(['name' => 'individual-leader']);
        $individual_diver = Role::firstOrCreate(['name' => 'individual-diver']);
        $individual_scientific = Role::firstOrCreate(['name' => 'individual-scientific']);
        $individual_sport = Role::firstOrCreate(['name' => 'individual-sport']);

        // LMS roles
        $individual_lms_instructor = Role::firstOrCreate(['name' => 'individual-lms-instructor']);

        $view_individual_coach = Role::firstOrCreate(['name' => 'view-individual-coach']);
        $view_individual_technical_official = Role::firstOrCreate(['name' => 'view-individual-technical-official']);

        $view_individual_diving_instructor = Role::firstOrCreate(['name' => 'view-individual-diving-instructor']);
        $view_individual_scientific_instructor = Role::firstOrCreate(['name' => 'view-individual-scientific-instructor']);
        $view_individual_diving_leader = Role::firstOrCreate(['name' => 'view-individual-diving-leader']);
        $view_individual_scientific_leader = Role::firstOrCreate(['name' => 'view-individual-scientific-leader']);

        $admin->syncPermissions([
            'access users',
            'manage user roles',
            'manage_menus',
            'access federations',
            'access memberships',
            'access products',
            'access entities',
            'access individuals',
            'access events',
            'access certifications',
            'access sport certifications',
            'access diving and scientific certifications',
            'access diving and scientific certifications manager',
            'access sport certifications attributed',
            'access sport certifications manager',
            'access scientific certifications attributed',
            'access diving certifications attributed',
            'access certification slots manager',
            'access licenses',
            'access licenses manager',
            'access diving certification attributed',
            'access sport individual licenses attributed',
            'access diving individual licenses attributed',
            'access scientific individual licenses attributed',
            'access sport entity licenses attributed',
            'access diving entity licenses attributed',
            'access scientific entity licenses attributed',
            'access documents',
            'create payment documents',
            'access official documents',
            'access attachments menu',
            'access diving attachments',
            'access sport attachments',
            'access scientific attachments',
            'access settings',
            'download reports',
            'access files area menu',
            'impersonate users',
        ]);

        $association_sport_admin->syncPermissions([
            'access federations',
            'access products',
            'access entities',
            'access individuals',
            'access events',
            'access certifications',
            'access sport certifications attributed',
            'access licenses',
            'access sport individual licenses attributed',
            'access sport entity licenses attributed',
            'access attachments menu',
            'access sport attachments',
            'access files area menu',
            'access official documents',
        ]);

        $association_admin->syncPermissions([
            'access federations',
            'access products',
            'access entities',
            'access individuals',
            'access events',
            'access certifications',
            'access diving and scientific certifications',
            'access diving certifications attributed',
            'access licenses',
            'access diving individual licenses attributed',
            'access diving entity licenses attributed',
            'access attachments menu',
            'access diving attachments',
            'access files area menu',
            'access official documents',
        ]);

        $association_scientific_admin->syncPermissions([
            'access federations',
            'access products',
            'access entities',
            'access individuals',
            'access events',
            'access certifications',
            'access diving and scientific certifications',
            'access scientific certifications attributed',
            'access licenses',
            'access scientific individual licenses attributed',
            'access scientific entity licenses attributed',
            'access attachments menu',
            'access scientific attachments',
            'access files area menu',
            'access official documents',
        ]);

        // Note: admin-notifications role exists in DB but not in simplified role system
        // Permissions for this role are not assigned here

        $federation_admin->syncPermissions([
            'access memberships',
            'access individuals',
            'access entities',
            'access documents',
            'access federation official documents',
            'access files area menu',
            'create entities',
            // Menu permissions for main federation to see all committees
            'access sport menu',
            'access diving menu',
            'access scientific menu',
            // Event management for main federation
            'access events',
            'manage-events',
        ]);

        // Note: Old federation roles (federation_sport_admin, federation_diver_admin, etc.)
        // have been merged into association roles by the migration.
        // Their permissions are preserved for existing databases via the migration.
        // For fresh installs, the association roles above receive appropriate permissions.

        $association_territorial_admin->syncPermissions([
            'access individuals',
            'access entities',
            'access events',
            'access files area menu',
            'create entities',
            'access official documents',
        ]);

        // Assign permissions for Entity Admin
        $entity_admin->syncPermissions([
            'access memberships',
            'access individuals',
            'access events',
            'access orders',
            'access files area menu',
        ]);

        // Assign permissions for Entity International
        $entity_international->syncPermissions([
            'access orders',
        ]);

        // Assign permissions for Entity Sports
        $entity_sport->syncPermissions([
            'access memberships',
            'access individuals',
            'access events',
            'access sport attachments',
            'access sport menu',
            'access files area menu',
        ]);

        // Assign permissions for Entity Diving Services
        $entity_diving_services->syncPermissions([
            'access memberships',
            'access individuals',
            'access events',
            'access diving attachments',
            'access diving menu',
            'access files area menu',
        ]);

        // Note: entity_scientific_admin and entity_cmas_operator roles have been removed
        // in the simplified role system. Their permissions are not assigned here.

        // Assign permissions for Individual Approved
        $individual_approved->syncPermissions([
            'access starter menu',
            'access orders',
            'access sport license menu',
            'access files area menu',
            'access athlete official documents',
            'access events',
        ]);

        // Assign permissions for Individual Coach
        $individual_coach->syncPermissions([
            'access coach menu',
            'access events',
            'access sport individual licenses attributed',
            'access coach official documents',
            'access files area menu',
        ]);

        // Assign permissions for Individual Instructor
        $individual_instructor->syncPermissions([
            'access instructor menu',
            'access events',
            'access diving individual licenses attributed',
            'access instructor-leader official documents',
            'access files area menu',
        ]);

        // Assign permissions for LMS Instructor
        $individual_lms_instructor->syncPermissions([
            'access lms menu',
            'access lms students',
            'access lms progression',
            'manage lms progression',
        ]);

        // Assign permissions for Individual Athlete
        $individual_athlete->syncPermissions([
            'access athlete menu',
            'access events',
            'access sport individual licenses attributed',
            'access athlete official documents',
            'access files area menu',
        ]);

        // Assign permissions for Individual Technical Official (merged judge and referee)
        $individual_technical_official->syncPermissions([
            'access judge menu',
            'access referee menu',
            'access events',
            'access sport individual licenses attributed',
            'access referee-judge official documents',
            'access files area menu',
        ]);

        // Assign permissions for Individual Leader
        $individual_leader->syncPermissions([
            'access leader menu',
            'access events',
            'access diving individual licenses attributed',
            'access instructor-leader official documents',
            'access files area menu',
        ]);

        // Assign permissions for Individual Diver
        $individual_diver->syncPermissions([
            'access diver menu',
            'access events',
            'access diving individual licenses attributed',
            'access diving certifications attributed',
            'access diving attachments',
            'access diving official documents',
            'access diver official documents',
            'access files area menu',
        ]);

        $individual_scientific->syncPermissions([
            'access scientific menu',
            'access diver menu',
            'access events',
            'access diving individual licenses attributed',
            'access scientific individual licenses attributed',
            'access scientific certifications attributed',
            'access scientific attachments',
            'access scientific official documents',
            'access files area menu',
        ]);

        $individual_sport->syncPermissions([
            'access sport menu',
            'access events',
            'access sport individual licenses attributed',
            'access sport certifications attributed',
            'access sport attachments',
            'access sport official documents',
            'access files area menu',
        ]);

        $view_individual_coach->syncPermissions([
            'access coach menu',
            'access sport menu',
        ]);

        $view_individual_technical_official->syncPermissions([
            'access judge menu',
            'access referee menu',
            'access sport menu',
        ]);

        $view_individual_diving_instructor->syncPermissions([
            'access diver menu',
            'access instructor menu',
        ]);

        $view_individual_scientific_instructor->syncPermissions([
            'access scientific menu',
            'access instructor menu',
        ]);

        $view_individual_diving_leader->syncPermissions([
            'access diver menu',
            'access leader menu',
        ]);

        $view_individual_scientific_leader->syncPermissions([
            'access scientific menu',
            'access leader menu',
        ]);
    }
}
