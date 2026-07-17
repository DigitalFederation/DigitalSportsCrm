<?php

/**
 * Admin back-office routes
 * --------------------------------------------------------------------------
 * Every route lives under:
 *      • prefix  : /admin
 *      • name    : admin.*
 *      • guard   : user_group:ADMIN
 */

use App\Http\Controllers\Admin\AffiliationController;
use App\Http\Controllers\Admin\AffiliationPlanController;
use App\Http\Controllers\Admin\ApplicationManagementController;
use App\Http\Controllers\Admin\CertificationAttributedController;
use App\Http\Controllers\Admin\CertificationCardController;
use App\Http\Controllers\Admin\CertificationController as AdminCertificationController;
use App\Http\Controllers\Admin\CertificationRoleMappingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
/* ------------------------------------------------------------------------
 |  Controller imports – kept together for easy scanning
 |  (⌘/Ctrl-F on the filename to jump straight here)
 * --------------------------------------------------------------------- */
use App\Http\Controllers\Admin\DocumentManualPaymentController;
use App\Http\Controllers\Admin\EntityController as AdminEntityController;
use App\Http\Controllers\Admin\EntityImportController;
use App\Http\Controllers\Admin\EvtEvents\AntiDopingController;
use App\Http\Controllers\Admin\EvtEvents\AttributeController;
use App\Http\Controllers\Admin\EvtEvents\AttributeGroupController;
use App\Http\Controllers\Admin\EvtEvents\AttributeRuleController;
use App\Http\Controllers\Admin\EvtEvents\CompetitionController;
use App\Http\Controllers\Admin\EvtEvents\DisciplineController;
use App\Http\Controllers\Admin\EvtEvents\DisciplineTemplateController;
use App\Http\Controllers\Admin\EvtEvents\Enrollments\AthleteEnrollmentController;
use App\Http\Controllers\Admin\EvtEvents\Enrollments\CoachEnrollmentController;
use App\Http\Controllers\Admin\EvtEvents\Enrollments\IndividualEnrollmentController;
use App\Http\Controllers\Admin\EvtEvents\Enrollments\RefereeEnrollmentController;
use App\Http\Controllers\Admin\EvtEvents\Enrollments\StaffEnrollmentController;
use App\Http\Controllers\Admin\EvtEvents\Enrollments\TeamOfficialEnrollmentController;
use App\Http\Controllers\Admin\EvtEvents\EventController;
use App\Http\Controllers\Admin\EvtEvents\EventExportController;
use App\Http\Controllers\Admin\EvtEvents\EventMasterController;
use App\Http\Controllers\Admin\EvtEvents\SportAgeGroupController;
use App\Http\Controllers\Admin\EvtEvents\SportController;
use App\Http\Controllers\Admin\FederationController as AdminFederationController;
use App\Http\Controllers\Admin\FederationRoleMappingController;
use App\Http\Controllers\Admin\FederationVotingRightController as AdminFederationVotingRightController;
use App\Http\Controllers\Admin\GeneratedReportsController;
use App\Http\Controllers\Admin\HomePageSettingsController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\IndividualController as AdminIndividualController;
use App\Http\Controllers\Admin\IndividualImportController;
use App\Http\Controllers\Admin\IndividualInsuranceReportController;
use App\Http\Controllers\Admin\InsuranceController as AdminInsuranceController;
use App\Http\Controllers\Admin\InsurancePlanController as AdminInsurancePlanController;
use App\Http\Controllers\Admin\LicenseAttributedController as AdminLicensesAttributedController;
use App\Http\Controllers\Admin\LicenseController as AdminLicenseController;
use App\Http\Controllers\Admin\LicenseRoleMappingController;
use App\Http\Controllers\Admin\MemberNumberSettingsController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\MembershipPackageController;
use App\Http\Controllers\Admin\MembershipPlanController;
use App\Http\Controllers\Admin\MemberSubscriptionController;
use App\Http\Controllers\Admin\MenuManagementController;
use App\Http\Controllers\Admin\OfficialDocumentsController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentTransactionController;
use App\Http\Controllers\Admin\PermissionManagementController;
use App\Http\Controllers\Admin\ProfessionalRoleController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\RoleMappingDashboardController;
use App\Http\Controllers\Admin\RoutePermissionController;
use App\Http\Controllers\Admin\SeparatedLicenseAttributedController as AdminSeparatedLicenseAttributedController;
use App\Http\Controllers\Admin\StaffProfessionalRolesController;
use App\Http\Controllers\Admin\SuspendLicenseAttributedController;
use App\Http\Controllers\Admin\UserMergeController;
use App\Http\Controllers\Admin\UsersController as AdminUsersController;
use App\Http\Controllers\Admin\WebhookLogController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\Common\DownloadEventMediaController;
use App\Http\Controllers\DownloadMediaController;
use App\Http\Controllers\Shared\InsuranceDocumentController as AdminInsuranceDocumentController;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

/* ------------------------------------------------------------------------
 |  Route definitions start just below this comment — the rest of the
 |  (already-working) file stays exactly as in the previous refactor.
 * --------------------------------------------------------------------- */

/* ------------------------------------------------------------------------ */
/*  Base Admin group */
/* ------------------------------------------------------------------------ */

Route::prefix('admin')
    ->middleware(['user_group:ADMIN'])
    ->name('admin.')
    ->group(function () {

        /* -------------------------------------------------------------------- */
        /*  General utilities */
        /* -------------------------------------------------------------------- */
        // Impersonation
        Route::get('impersonate/start/{id}', [ImpersonateController::class, 'start'])
            ->name('impersonate.start');

        // Dashboard
        Route::get('dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /* -------------------------------------------------------------------- */
        /*  Insurance */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access memberships')->group(function () {

            // Insurance Plans
            Route::resource('insurance-plans', AdminInsurancePlanController::class)
                ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

            Route::get(
                'insurance-plans/{id}/download/{mediaId}',
                [AdminInsurancePlanController::class, 'downloadAttachment']
            )->name('insurance-plans.download');

            // Insurances (records)
            Route::controller(AdminInsuranceController::class)->group(function () {
                Route::get('insurances', 'index')->name('insurances.index');
                Route::get('insurances/{insurance}', 'show')->name('insurances.show');
                Route::get('insurances/{insurance}/edit', 'edit')->name('insurances.edit');
                Route::put('insurances/{insurance}', 'update')->name('insurances.update');
                Route::patch('insurances/{insurance}/status', 'updateStatus')->name('insurances.update-status');
                Route::delete('insurances/{insurance}', 'destroy')->name('insurances.destroy');
            });

            // Insurance Reports (standalone export management)
            Route::get('insurance-reports', [IndividualInsuranceReportController::class, 'index'])
                ->name('insurance-reports.index');

            // Insurance Documents
            Route::prefix('insurances/{insurance}/document')->group(function () {
                Route::get('/', [AdminInsuranceDocumentController::class, 'show'])
                    ->name('insurances.document.show');
                Route::get('download', [AdminInsuranceDocumentController::class, 'download'])
                    ->name('insurances.document.download');
                Route::get('conditions', [AdminInsuranceDocumentController::class, 'downloadConditions'])
                    ->name('insurances.document.conditions');
            });
        });

        /* -------------------------------------------------------------------- */
        /*  Membership management */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access memberships')->group(function () {

            /* Membership-Packages --------------------------------------------- */
            Route::controller(MembershipPackageController::class)
                ->name('membership-packages.')
                ->group(function () {
                    Route::get('/membership-packages', 'index')->name('index');
                    Route::get('/membership-package/create', 'create')->name('create');
                    Route::post('/membership-package', 'store')->name('store');
                    Route::get('/membership-package/{package}', 'show')->name('show');
                    Route::get('/membership-package/{package}/edit', 'edit')->name('edit');
                    Route::put('/membership-package/{package}', 'update')->name('update');
                    Route::delete('/membership-package/{package}', 'destroy')->name('delete');
                });

            /* Affiliation-Plans ------------------------------------------------ */
            Route::controller(AffiliationPlanController::class)
                ->name('affiliation-plans.')
                ->group(function () {
                    Route::get('/affiliation-plans', 'index')->name('index');
                    Route::get('/affiliation-plan/create', 'create')->name('create');
                    Route::post('/affiliation-plan', 'store')->name('store');
                    Route::get('/affiliation-plan/{plan}', 'show')->name('show');
                    Route::get('/affiliation-plan/{plan}/edit', 'edit')->name('edit');
                    Route::put('/affiliation-plan/{plan}', 'update')->name('update');
                    Route::delete('/affiliation-plan/{plan}', 'destroy')->name('delete');
                });

            /* Affiliations ----------------------------------------------------- */
            Route::controller(AffiliationController::class)
                ->name('affiliations.')
                ->group(function () {
                    Route::get('/affiliations', 'index')->name('index');
                    Route::patch('/affiliation/{affiliation}/status', 'updateStatus')->name('update-status');
                    Route::delete('/affiliation/{affiliation}', 'destroy')->name('destroy');
                });

            /* Member-Subscriptions -------------------------------------------- */
            Route::controller(MemberSubscriptionController::class)
                ->name('member-subscriptions.')
                ->group(function () {
                    Route::get('/member-subscriptions', 'index')->name('index');
                    Route::get('/member-subscription/create', 'create')->name('create');
                    Route::post('/member-subscription', 'store')->name('store');
                    Route::get('/member-subscription/{subscription}', 'show')->name('show');
                    Route::put('/member-subscription/{subscription}/renew', 'renew')->name('renew');
                    Route::patch('/member-subscription/{subscription}/status', 'updateStatus')->name('update-status');
                    Route::delete('/member-subscription/{subscription}', 'destroy')->name('destroy');
                });
        });

        /* -------------------------------------------------------------------- */
        /*  Reports & Statistics */
        /* -------------------------------------------------------------------- */
        Route::controller(GeneratedReportsController::class)->name('reports.')->group(function () {
            Route::get('reports', 'index')->name('index');
            Route::get('statistics', 'statistics')->name('stats');
            Route::post('reports', 'store')
                ->name('store')
                ->middleware(ThrottleRequests::class . ':4,1');
            Route::delete('reports/{report}', 'destroy')->name('delete');
            Route::get('reports/check-status', 'checkStatus')->name('check-status');
            Route::get('download-report/{report}', 'download')->name('download');
        });

        /* -------------------------------------------------------------------- */
        /*  Anti-Doping PINs */
        /* -------------------------------------------------------------------- */
        Route::controller(AntiDopingController::class)->group(function () {
            Route::get('antidoping/pin/create', 'create')->name('anti-doping.pin.create');
            Route::post('antidoping/pin', 'store')->name('anti-doping.pin.store');
            Route::delete('antidoping/pin/{id}', 'destroy')->name('anti-doping.pin.destroy');
        });

        /* -------------------------------------------------------------------- */
        /*  Media (generic download / delete) */
        /* -------------------------------------------------------------------- */
        Route::controller(DownloadMediaController::class)->group(function () {
            Route::post('download', 'download')->name('media.download');
            Route::post('delete', 'delete')->name('media.delete');
        });

        /* -------------------------------------------------------------------- */
        /*  Official documents */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access official documents')
            ->controller(OfficialDocumentsController::class)
            ->prefix('official-documents')
            ->name('official-documents.')
            ->group(function () {
                Route::get('edit/{id}', 'edit')->name('edit');
                Route::get('preview/{id}', 'preview')->name('preview');
                Route::put('{id}/activate', 'activate')->name('activate');
                Route::put('{id}/reject', 'reject')->name('reject');
                Route::post('{id}', 'download')->name('download');
                Route::put('{id}', 'update')->name('update');
                Route::delete('{id}', 'destroy')->name('delete');
                Route::get('{type}', 'index')->name('index');
            });

        /* -------------------------------------------------------------------- */
        /*  Roles & Users */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access users')->group(function () {

            // Legacy roles routes removed - use role-management instead

            // Dynamic Role Management System
            Route::prefix('role-management')->name('role-management.')->middleware('permission:access users')->group(function () {
                Route::get('/', [RoleManagementController::class, 'index'])->name('index');
                Route::get('/create', [RoleManagementController::class, 'create'])->name('create');
                Route::post('/', [RoleManagementController::class, 'store'])->name('store');
                Route::get('/{role}', [RoleManagementController::class, 'show'])->name('show');
                Route::get('/{role}/edit', [RoleManagementController::class, 'edit'])->name('edit');
                Route::put('/{role}', [RoleManagementController::class, 'update'])->name('update');
                Route::delete('/{role}', [RoleManagementController::class, 'destroy'])->name('destroy');
                Route::post('/{role}/duplicate', [RoleManagementController::class, 'duplicate'])->name('duplicate');
                Route::get('/{role}/permissions', [RoleManagementController::class, 'permissions'])->name('permissions');
                Route::post('/{role}/permissions/assign', [RoleManagementController::class, 'assignPermissions'])->name('permissions.assign');
                Route::post('/{role}/permissions/sync', [RoleManagementController::class, 'syncPermissions'])->name('permissions.sync');
                Route::put('/{role}/permissions', [RoleManagementController::class, 'syncPermissions'])->name('permissions.update');
                Route::get('/search/roles', [RoleManagementController::class, 'searchRoles'])->name('search.roles');
                Route::get('/api/statistics', [RoleManagementController::class, 'getStatistics'])->name('statistics');
            });

            // Role Mappings
            Route::prefix('role-mappings')->name('role-mappings.')->group(function () {
                Route::get('/', [RoleMappingDashboardController::class, 'index'])->name('index');

                // License Role Mappings
                Route::prefix('licenses')->name('licenses.')->group(function () {
                    Route::get('/', [LicenseRoleMappingController::class, 'index'])->name('index');
                });

                // Certification Role Mappings
                Route::prefix('certifications')->name('certifications.')->group(function () {
                    Route::get('/', [CertificationRoleMappingController::class, 'index'])->name('index');
                });

                // Federation Role Mappings
                Route::prefix('federations')->name('federations.')->group(function () {
                    Route::get('/', [FederationRoleMappingController::class, 'index'])->name('index');
                });
            });

            // Dynamic Permission Management System
            Route::prefix('permission-management')->name('permission-management.')->middleware('permission:manage-permissions|view-permissions')->group(function () {
                Route::get('/', [PermissionManagementController::class, 'index'])->name('index');
                Route::get('/create', [PermissionManagementController::class, 'create'])->name('create');
                Route::post('/', [PermissionManagementController::class, 'store'])->name('store');
                Route::get('/bulk-create', [PermissionManagementController::class, 'bulkCreate'])->name('bulk-create');
                Route::post('/bulk-store', [PermissionManagementController::class, 'bulkStore'])->name('bulk-store');
                Route::get('/import', [PermissionManagementController::class, 'import'])->name('import');
                Route::post('/import', [PermissionManagementController::class, 'processImport'])->name('process-import');
                Route::get('/export', [PermissionManagementController::class, 'export'])->name('export');
                Route::get('/{permission}', [PermissionManagementController::class, 'show'])->name('show');
                Route::get('/{permission}/edit', [PermissionManagementController::class, 'edit'])->name('edit');
                Route::put('/{permission}', [PermissionManagementController::class, 'update'])->name('update');
                Route::delete('/{permission}', [PermissionManagementController::class, 'destroy'])->name('destroy');
                Route::get('/ajax/search', [PermissionManagementController::class, 'search'])->name('search');
                Route::get('/ajax/statistics', [PermissionManagementController::class, 'statistics'])->name('statistics');
            });

            // Route Permission Management System
            Route::prefix('route-permissions')->name('route-permissions.')->group(function () {
                Route::get('/', [RoutePermissionController::class, 'index'])->name('index');
                Route::post('/quick-assign', [RoutePermissionController::class, 'quickAssign'])->name('quick-assign');
                Route::put('/{routePermission}', [RoutePermissionController::class, 'update'])->name('update');
                Route::delete('/{routePermission}', [RoutePermissionController::class, 'destroy'])->name('destroy');
                Route::get('/suggest', [RoutePermissionController::class, 'suggest'])->name('suggest');
                Route::get('/preview', [RoutePermissionController::class, 'preview'])->name('preview');
                Route::get('/export', [RoutePermissionController::class, 'export'])->name('export');
            });

            // Menu Management
            Route::prefix('menu-management')->name('menu-management.')->group(function () {
                Route::get('/', [MenuManagementController::class, 'index'])->name('index');
            });

            // Users
            Route::controller(AdminUsersController::class)->group(function () {
                Route::get('users', 'index')->name('users.index');
                Route::get('user/create', 'create')->name('user.create');
                Route::post('user', 'store')->name('user.store');
                Route::get('user/{user}/edit', 'edit')->name('user.edit');
                Route::put('user/{user}', 'update')->name('user.update');
                Route::delete('user/{user}', 'destroy')->name('user.delete');

                // resend invitation
                Route::post('users/{id}/resend-email', 'resendUserCreationEmail')
                    ->name('users.resend-email')
                    ->middleware(ThrottleRequests::class . ':2,1');
            });

            // User merge
            Route::controller(UserMergeController::class)
                ->middleware('permission:manage user roles')
                ->group(function () {
                    Route::get('users/merge', 'show')->name('users.merge.form');
                    Route::post('users/merge-preview', 'preview')->name('users.merge.preview');
                    Route::post('users/merge', 'merge')->name('users.merge');
                });
        });

        /* -------------------------------------------------------------------- */
        /*  Federations & Voting Rights */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access federations')->group(function () {

            // Federations
            Route::controller(AdminFederationController::class)->group(function () {
                Route::get('federations', 'index')->name('federation.index');
                Route::get('federation/create', 'create')->name('federation.create');
                Route::post('federation', 'store')->name('federation.store');
                Route::get('federation/{federation}', 'show')->name('federation.show');
                Route::get('federation/{federation}/edit', 'edit')->name('federation.edit');
                Route::put('federation/{federation}', 'update')->name('federation.update');
                Route::delete('federation/{federation}', 'destroy')->name('federation.delete');

                // extras
                Route::get('federation/{federation}/files', 'files')->name('federation.files');
                Route::get('federation/export', 'export')->name('federation.export');
                Route::post('federation/upload', 'upload')->name('federation.upload');

                // License management
                Route::get('federation/{federation}/licenses', 'licenses')->name('federation.licenses');

                // Committee management
                Route::get('federation/{federation}/committees', 'committees')->name('federation.committees');

                // Welcome email
                Route::post('federation/{federation}/send-welcome-email', 'sendWelcomeEmail')
                    ->name('federation.send-welcome-email')
                    ->middleware(\Illuminate\Routing\Middleware\ThrottleRequests::class . ':2,1');
            });

            // Voting rights
            Route::controller(AdminFederationVotingRightController::class)->group(function () {
                Route::get('federation-voting-rights', 'index')->name('federation-voting-right.index');
                Route::get('federation-voting-rights/export', 'export')->name('federation-voting-right.export');
            });
        });

        /* -------------------------------------------------------------------- */
        /*  Entities & Individuals */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access entities')->group(function () {
            // Entity Import Routes (must come BEFORE resource routes to avoid conflicts)
            Route::prefix('entity/import')->name('entity.import.')->group(function () {
                Route::get('/', [EntityImportController::class, 'index'])->name('index');
                Route::post('upload', [EntityImportController::class, 'upload'])->name('upload');
                Route::get('analyze', [EntityImportController::class, 'analyze'])->name('analyze');
                Route::get('preview', [EntityImportController::class, 'preview'])->name('preview');
                Route::post('validate-mapping', [EntityImportController::class, 'validateMapping'])->name('validate-mapping');
                Route::post('execute', [EntityImportController::class, 'import'])->name('execute');
                Route::get('progress', [EntityImportController::class, 'progress'])->name('progress');
                Route::get('template', [EntityImportController::class, 'downloadTemplate'])->name('template');
                Route::get('errors', [EntityImportController::class, 'downloadErrors'])->name('errors');
            });

            Route::resource('entities', AdminEntityController::class)
                ->except(['show'])
                ->names('entity');
            Route::get('entity/{entity}', [AdminEntityController::class, 'show'])
                ->name('entity.show');
            Route::post('entity/{entity}/send-welcome-email', [AdminEntityController::class, 'sendWelcomeEmail'])
                ->name('entity.send-welcome-email')
                ->middleware(\Illuminate\Routing\Middleware\ThrottleRequests::class . ':2,1');
        });

        Route::middleware('permission:access individuals')->group(function () {
            // Individual Import Routes (must come BEFORE resource routes to avoid conflicts)
            Route::prefix('individual/import')->name('individual.import.')->group(function () {
                Route::get('/', [IndividualImportController::class, 'index'])->name('index');
                Route::post('upload', [IndividualImportController::class, 'upload'])->name('upload');
                Route::post('validate-mapping', [IndividualImportController::class, 'validateMapping'])->name('validate-mapping');
                Route::post('execute', [IndividualImportController::class, 'import'])->name('execute');
                Route::get('template', [IndividualImportController::class, 'downloadTemplate'])->name('template');
                Route::get('error-report', [IndividualImportController::class, 'downloadErrorReport'])->name('error-report');
                Route::get('progress/{importId}', [IndividualImportController::class, 'getProgress'])->name('progress');
                Route::post('cancel/{importId}', [IndividualImportController::class, 'cancel'])->name('cancel');
            });

            // Individual CRUD Routes (explicitly defined to avoid conflicts)
            Route::get('individual', [AdminIndividualController::class, 'index'])->name('individual.index');
            Route::get('individual/create', [AdminIndividualController::class, 'create'])->name('individual.create');
            Route::post('individual', [AdminIndividualController::class, 'store'])->name('individual.store');
            Route::get('individual/{individual}', [AdminIndividualController::class, 'show'])->name('individual.show');
            Route::get('individual/{individual}/edit', [AdminIndividualController::class, 'edit'])->name('individual.edit');
            Route::put('individual/{individual}', [AdminIndividualController::class, 'update'])->name('individual.update');
            Route::patch('individual/{individual}', [AdminIndividualController::class, 'update']);
            Route::delete('individual/{individual}', [AdminIndividualController::class, 'destroy'])->name('individual.destroy');
            Route::get('individual/{individual}/files', [AdminIndividualController::class, 'files'])->name('individual.files');
            Route::get('individual/{individual}/update-email', [AdminIndividualController::class, 'showUpdateEmailForm'])->name('individual.show-update-email');
            Route::put('individual/{individual}/update-email', [AdminIndividualController::class, 'updateEmail'])->name('individual.update-email');
            Route::post('individual/{individual}/send-welcome-email', [AdminIndividualController::class, 'sendWelcomeEmail'])
                ->name('individual.send-welcome-email')
                ->middleware(\Illuminate\Routing\Middleware\ThrottleRequests::class . ':2,1');
        });

        /* -------------------------------------------------------------------- */
        /*  Memberships */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access memberships')->group(function () {

            Route::resource('membership-plans', MembershipPlanController::class)
                ->names('membership-plan');

            Route::controller(MembershipController::class)->group(function () {
                Route::get('memberships', 'index')->name('membership.index');
                Route::get('membership/create', 'create')->name('membership.create');
                Route::post('membership', 'store')->name('membership.store');
                Route::get('membership/{membership}', 'show')->name('membership.show');
                Route::get('membership/{membership}/edit', 'edit')->name('membership.edit');
                Route::put('membership/{membership}', 'update')->name('membership.update');
                Route::delete('membership/{membership}', 'destroy')->name('membership.delete');
                Route::get('membership/{id}/activate', 'activate')->name('membership.activate');
            });
        });

        /* -------------------------------------------------------------------- */
        /*  Certifications */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access certifications')->group(function () {

            // Dedicated committee-specific routes (server-side enforced)
            Route::get('certifications/diving', [AdminCertificationController::class, 'indexDiving'])
                ->name('certification.diving');
            Route::get('certifications/sport', [AdminCertificationController::class, 'indexSport'])
                ->name('certification.sport');

            // main CRUD
            Route::resource('certifications', AdminCertificationController::class)
                ->names('certification')
                ->except(['show']);

            // card download
            Route::get(
                'certification-card/{certificationAttributed}/download',
                [CertificationCardController::class, 'download']
            )->name('certification-card.download');
        });

        // Attributed certifications (requires multiple permissions)
        Route::controller(CertificationAttributedController::class)
            ->middleware('permission:access sport certifications attributed|access scientific certifications attributed|access diving certifications attributed')
            ->prefix('certification-attributed')
            ->name('certification-attributed.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{certification}', 'show')->name('show');
                Route::get('{certification_attributed}/edit', 'edit')->name('edit');
                Route::put('{certification_attributed}', 'update')->name('update');
                Route::delete('{certification_attributed}', 'destroy')->name('delete');

                // actions
                Route::post('activate', 'activate')->name('activate');
                Route::post('suspend', 'suspend')->name('suspend');
                Route::post('unsuspend', 'unsuspend')->name('unsuspend');
                Route::post('cancel', 'cancel')->name('cancel');
            });

        /* -------------------------------------------------------------------- */
        /*  Licenses */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access licenses')->group(function () {
            Route::resource('licenses', AdminLicenseController::class)
                ->names('license')
                ->parameters(['licenses' => 'license'])
                ->except(['show']);

            // License Certification Requirements
            Route::controller(\App\Http\Controllers\Admin\LicenseCertificationRequirementsController::class)
                ->prefix('licenses/{license}/certification-requirements')
                ->name('license-certification-requirements.')
                ->group(function () {
                    Route::get('/', 'show')->name('show');
                    Route::get('/edit', 'edit')->name('edit');
                    Route::put('/', 'update')->name('update');
                });

            // Attributed licenses
            Route::controller(AdminLicensesAttributedController::class)
                ->prefix('license-attributed')
                ->name('license-attributed.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create/{license_type_name}/{committee}', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{id}', 'show')->name('show');
                    Route::put('{id}/activate', 'activate')->name('activate');
                    Route::put('{id}/cancel', 'cancel')->name('cancel');
                    Route::put('{id}/approve', 'approve')->name('approve');
                    Route::put('provisional', 'provisional')->name('provisional');
                    Route::delete('{id}', 'destroy')->name('delete');
                });

            // Suspend license (store-only resource)
            Route::resource('license-suspend', SuspendLicenseAttributedController::class)
                ->only(['store']);

            // Separated License Attributed Routes, generated from
            // config/committees.php: `admin.{slug}-{holder}-licenses-attributed.index`
            // for each committee and holder (entity/individual).
            Route::controller(AdminSeparatedLicenseAttributedController::class)->group(function () {
                foreach (\App\Support\Committees::holderAttributedRoutes() as $attributed) {
                    Route::get("/{$attributed['slug']}-{$attributed['holder']}-licenses-attributed", 'show')
                        ->defaults('committeeCode', $attributed['code'])
                        ->defaults('holder', $attributed['holder'])
                        ->name("{$attributed['slug']}-{$attributed['holder']}-licenses-attributed.index");
                }
            });
        });

        // Staff professional roles (settings) - controller only has index, store, destroy
        Route::resource('staff-roles', StaffProfessionalRolesController::class)
            ->only(['index', 'store', 'destroy'])
            ->middleware('permission:access settings');

        // Member number settings
        Route::prefix('member-number-settings')
            ->name('member-number-settings.')
            ->middleware('permission:access settings')
            ->group(function () {
                Route::get('/', [MemberNumberSettingsController::class, 'index'])->name('index');
                Route::put('/', [MemberNumberSettingsController::class, 'update'])->name('update');
            });

        // Professional roles management
        Route::prefix('professional-roles')
            ->name('professional-roles.')
            ->middleware('permission:access settings')
            ->group(function () {
                Route::get('/', [ProfessionalRoleController::class, 'index'])->name('index');
                Route::get('/create', [ProfessionalRoleController::class, 'create'])->name('create');
                Route::post('/', [ProfessionalRoleController::class, 'store'])->name('store');
                Route::get('/{professionalRole}/edit', [ProfessionalRoleController::class, 'edit'])->name('edit');
                Route::put('/{professionalRole}', [ProfessionalRoleController::class, 'update'])->name('update');
                Route::delete('/{professionalRole}', [ProfessionalRoleController::class, 'destroy'])->name('destroy');
            });

        /* -------------------------------------------------------------------- */
        /*  Documents & Manual Payments */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access documents')->group(function () {
            Route::controller(AdminDocumentController::class)->group(function () {

                Route::get('documents', 'index')->name('document.index');
                Route::get('documents/invoices', 'invoices')->name('document.invoices');
                Route::get('documents/invoices/export', 'exportInvoices')->name('document.invoices.export');

                Route::get('document/create', 'create')->name('document.create');
                Route::post('document/{document}/cancel', 'cancel')->name('document.cancel');
                Route::get('document/download/{id}', 'download')->name('document.download');
                Route::get('document/{document}/notify', 'notify')
                    ->name('document.notify')
                    ->middleware('throttle:1,1');

                Route::get('documents/{id}/edit', 'edit')->name('document.edit');
                Route::get('document/{id}/moloni-pdf', \App\Http\Controllers\Shared\MoloniDocumentPdfController::class)->name('document.moloni-pdf');
                Route::get('document/{id}', 'show')->name('document.show');
                Route::delete('document/{id}', 'destroy')->name('document.delete');
                Route::delete('documents/{id}/delete-canceled', 'deleteCanceledDocument')->name('document.delete-canceled');
            });
        });

        Route::post('document/{document}/manual-payment', [DocumentManualPaymentController::class, 'store'])
            ->name('document.manual-payment')
            ->middleware('permission:create payment documents');

        /* -------------------------------------------------------------------- */
        /*  Payment Administration */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access documents')->group(function () {

            // Payment Methods
            Route::controller(PaymentMethodController::class)
                ->prefix('payment-methods')
                ->name('payment-methods.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('{paymentMethod}/edit', 'edit')->name('edit');
                    Route::put('{paymentMethod}', 'update')->name('update');
                    Route::patch('{paymentMethod}/toggle', 'toggle')->name('toggle');
                });

            // Payment Transactions
            Route::controller(PaymentTransactionController::class)
                ->prefix('payment-transactions')
                ->name('payment-transactions.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('{transaction}', 'show')->name('show');
                });

            // Webhook Logs
            Route::controller(WebhookLogController::class)
                ->prefix('webhook-logs')
                ->name('webhook-logs.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('{webhookLog}', 'show')->name('show');
                });

            // Moloni Integration Settings
            Route::controller(\App\Http\Controllers\Admin\MoloniSettingsController::class)
                ->prefix('moloni-settings')
                ->name('moloni-settings.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/authorize', 'redirectToAuthorize')->name('authorize');
                    Route::get('/callback', 'callback')->name('callback');
                    Route::post('/disconnect', 'disconnect')->name('disconnect');
                    Route::post('/sync-data', 'syncData')->name('sync-data');
                    Route::post('/test-connection', 'testConnection')->name('test');
                    Route::post('/save', 'save')->name('save');
                    Route::post('/invoice-generation-rules', 'saveInvoiceGenerationRules')->name('invoice-generation-rules');
                    Route::post('/retry-invoice', 'retryInvoice')->name('retry-invoice');
                    Route::post('/bulk-retry-invoices', 'bulkRetryInvoices')->name('bulk-retry-invoices');
                    Route::post('/sync-customer', 'syncCustomer')->name('sync-customer');
                    Route::get('/invoice/{moloniInvoice}/pdf', 'downloadPdf')->name('invoice.pdf');
                    Route::post('/invoice/{moloniInvoice}/refresh', 'refreshStatus')->name('invoice.refresh');
                });

            // Integration Issues Dashboard (consolidated view of Moloni + Easypay errors)
            Route::controller(\App\Http\Controllers\Admin\IntegrationIssuesController::class)
                ->prefix('integration-issues')
                ->name('integration-issues.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                });
        });

        /* -------------------------------------------------------------------- */
        /*  Diving Professional Licensing */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access diving certifications attributed')->group(function () {
            // Professional Certifications
            Route::resource('diving-professional-certifications', \App\Http\Controllers\Admin\DivingProfessionalCertificationController::class)
                ->names('diving_professional_certifications')
                ->parameters(['diving-professional-certifications' => 'certification'])
                ->only(['index', 'show']);
            Route::put('diving-professional-certifications/{certification}/approve', [\App\Http\Controllers\Admin\DivingProfessionalCertificationController::class, 'approve'])
                ->name('diving_professional_certifications.approve');
            Route::put('diving-professional-certifications/{certification}/reject', [\App\Http\Controllers\Admin\DivingProfessionalCertificationController::class, 'reject'])
                ->name('diving_professional_certifications.reject');
            Route::put('diving-professional-certifications/{certification}/revoke', [\App\Http\Controllers\Admin\DivingProfessionalCertificationController::class, 'revoke'])
                ->name('diving_professional_certifications.revoke');
            Route::patch('diving-professional-certifications/{certification}', [\App\Http\Controllers\Admin\DivingProfessionalCertificationController::class, 'update'])
                ->name('diving_professional_certifications.update');
            Route::delete('diving-professional-certifications/{certification}', [\App\Http\Controllers\Admin\DivingProfessionalCertificationController::class, 'destroy'])
                ->name('diving_professional_certifications.destroy');
            Route::get('diving-professional-certifications/{certification}/download-document', [\App\Http\Controllers\Admin\DivingProfessionalCertificationController::class, 'downloadDocument'])
                ->name('diving_professional_certifications.download_document');

            // Technical Director Invitations
            Route::resource('diving-technical-director-invitations', \App\Http\Controllers\Admin\DivingTechnicalDirectorInvitationController::class)
                ->names('diving_technical_director_invitations')
                ->parameters(['diving-technical-director-invitations' => 'invitation'])
                ->only(['index', 'show']);
            Route::put('diving-technical-director-invitations/{invitation}/cancel', [\App\Http\Controllers\Admin\DivingTechnicalDirectorInvitationController::class, 'cancel'])
                ->name('diving_technical_director_invitations.cancel');

            // Diving Professionals
            Route::resource('diving-professionals', \App\Http\Controllers\Admin\DivingProfessionalsController::class)
                ->names('diving_professionals')
                ->only(['index', 'show']);

            // Entity Diving License Validation
            Route::prefix('entity-diving-license-validation')->name('entity_diving_license_validation.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'entityIndex'])
                    ->name('index');
                Route::get('{licenseAttributed}', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'entityShow'])
                    ->name('show');
                Route::get('{licenseAttributed}/pdf', [\App\Http\Controllers\Admin\DivingLicensePdfController::class, 'show'])
                    ->name('pdf')
                    ->middleware('throttle:10,1');
                Route::post('{licenseAttributed}/approve', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'entityApprove'])
                    ->name('approve');
                Route::post('{licenseAttributed}/reject', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'entityReject'])
                    ->name('reject');
                Route::delete('{licenseAttributed}', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'entityDestroy'])
                    ->name('destroy');
            });

            // Individual Diving License Validation
            Route::prefix('individual-diving-license-validation')->name('individual_diving_license_validation.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'individualIndex'])
                    ->name('index');
                Route::get('{licenseAttributed}', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'individualShow'])
                    ->name('show');
                Route::get('{licenseAttributed}/pdf', [\App\Http\Controllers\Admin\DivingLicensePdfController::class, 'show'])
                    ->name('pdf')
                    ->middleware('throttle:10,1');
                Route::post('{licenseAttributed}/approve', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'individualApprove'])
                    ->name('approve');
                Route::post('{licenseAttributed}/reject', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'individualReject'])
                    ->name('reject');
                Route::delete('{licenseAttributed}', [\App\Http\Controllers\Admin\SeparatedDivingLicenseValidationController::class, 'individualDestroy'])
                    ->name('destroy');
            });
        });

        /* -------------------------------------------------------------------- */
        /*  Attachments */
        /* -------------------------------------------------------------------- */

        Route::prefix('committee/{committee?}')->name('committee.')->group(function () {
            Route::resource('attachments', \App\Http\Controllers\Admin\AttachmentsController::class)
                ->except(['show']);
        });

        // Routes for no committees (null committee)
        Route::resource('attachments', \App\Http\Controllers\Admin\AttachmentsController::class)
            ->parameters(['attachments' => 'attachment'])
            ->except(['show']);

        /* -------------------------------------------------------------------- */
        /*  EVT-Events (big subsystem) */
        /* -------------------------------------------------------------------- */
        Route::prefix('evt-events')
            ->name('evt-events.')
            ->middleware('permission:access events')
            ->group(function () {

                /* Attribute & rule management -------------------------------- */
                Route::resource('attributes', AttributeController::class);
                Route::resource('attribute-group', AttributeGroupController::class)
                    ->except(['show']); // Controller has no show() method
                Route::resource('discipline-templates', DisciplineTemplateController::class)
                    ->except(['show']); // Controller has no show() method

                Route::prefix('attributes/{attribute?}')->group(function () {
                    Route::resource('attribute-rules', AttributeRuleController::class)
                        ->except(['show']);
                });

                /* Masters / exports ------------------------------------------ */
                Route::get('events/master', [EventMasterController::class, 'index'])->name('events.master.index');
                Route::get('events/master/get', [EventMasterController::class, 'getEvents'])->name('events.master.get');
                Route::post('events/master/update', [EventMasterController::class, 'updateEvent'])->name('events.master.update');

                Route::get('events/export', [EventController::class, 'masterexport'])
                    ->middleware('throttle:3,1')
                    ->name('events.export');

                /* Base events CRUD ------------------------------------------- */
                Route::resource('events', EventController::class)->except(['create']);
                Route::get('events/create/{category?}', [EventController::class, 'create'])
                    ->name('events.create');

                /* Stand-alone disciplines ------------------------------------ */
                Route::resource('disciplines', DisciplineController::class);
                Route::post('disciplines/{discipline}/duplicate', [DisciplineController::class, 'duplicate'])
                    ->name('disciplines.duplicate');

                /* Sport age groups ------------------------------------------- */
                Route::resource('sport-age-groups', SportAgeGroupController::class);

                /* Technical Official assignments report ----------------------- */
                Route::get('technical-official-assignments', [App\Http\Controllers\Admin\EvtEvents\TechnicalOfficialAssignmentsController::class, 'index'])
                    ->name('technical-official-assignments.index');
                Route::post('technical-official-assignments/export', [App\Http\Controllers\Admin\EvtEvents\TechnicalOfficialAssignmentsController::class, 'export'])
                    ->middleware('throttle:3,1')
                    ->name('technical-official-assignments.export');

                /* Referee enrollments history -------------------------------- */
                Route::get('referee-enrollments-history', [App\Http\Controllers\Admin\EvtEvents\RefereeEnrollmentsHistoryController::class, 'index'])
                    ->name('referee-enrollments-history.index');

                /* Coach enrollments history ---------------------------------- */
                Route::get('coach-enrollments-history', [App\Http\Controllers\Admin\EvtEvents\CoachEnrollmentsHistoryController::class, 'index'])
                    ->name('coach-enrollments-history.index');

                /* Nested resources under a specific event -------------------- */
                Route::prefix('events/{event}')->name('events.')->group(function () {

                    /* Media */
                    Route::resource('download-media', DownloadEventMediaController::class)->only(['store']);

                    /* Event reports and referee function assignments */
                    Route::get('reports', [App\Http\Controllers\Admin\EvtEvents\EventReportsController::class, 'show'])
                        ->name('reports');
                    Route::post('reports/export-excel', [App\Http\Controllers\Admin\EvtEvents\EventReportsController::class, 'exportExcel'])
                        ->middleware('throttle:3,1')
                        ->name('reports.export-excel');
                    Route::post('reports/export-pdf', [App\Http\Controllers\Admin\EvtEvents\EventReportsController::class, 'exportPdf'])
                        ->middleware('throttle:3,1')
                        ->name('reports.export-pdf');

                    /* Event-specific attributes */
                    Route::resource('attributes', AttributeController::class);

                    /* Staff enrollments */
                    Route::resource('staff-enrollment', StaffEnrollmentController::class)
                        ->only(['index', 'create', 'store', 'destroy']);
                    Route::get('staff-enrollment/export', [StaffEnrollmentController::class, 'export'])
                        ->middleware('throttle:3,1')
                        ->name('staff-enrollment.export');

                    /* Referee enrollments */
                    Route::resource('referee-enrollment', RefereeEnrollmentController::class)
                        ->only(['index', 'create', 'store', 'destroy']);
                    Route::get('referee-enrollment/export', [RefereeEnrollmentController::class, 'export'])
                        ->middleware('throttle:3,1')
                        ->name('referee-enrollment.export');

                    /* Team-official enrollments */
                    Route::resource('officials-enrollment', TeamOfficialEnrollmentController::class)
                        ->only(['index', 'destroy']);
                    Route::get('officials-enrollment/registered', [TeamOfficialEnrollmentController::class, 'registered'])
                        ->name('officials-enrollment.registered');
                    Route::delete('officials-enrollment/{officials_enrollment}/force-delete', [TeamOfficialEnrollmentController::class, 'forceDelete'])
                        ->name('officials-enrollment.force-delete');
                    Route::post('officials-enrollment/export', [TeamOfficialEnrollmentController::class, 'export'])
                        ->middleware('throttle:3,1')
                        ->name('officials-enrollment.export');

                    /* Athlete / individual / coach enrollments -------------- */
                    Route::prefix('enrollments')->name('enrollments.')->group(function () {

                        // Athlete
                        Route::resource('athlete', AthleteEnrollmentController::class)->only(['index']);
                        Route::get('athlete/registered', [AthleteEnrollmentController::class, 'registered'])
                            ->name('athlete.registered');
                        Route::put('{athleteEnrollment}/status', [AthleteEnrollmentController::class, 'updateStatus'])
                            ->name('athlete.update-status');
                        Route::post('{athleteEnrollment}/payment', [AthleteEnrollmentController::class, 'generatePaymentDocument'])
                            ->name('athlete.generate-payment');
                        Route::delete('athlete/{athleteEnrollment}', [AthleteEnrollmentController::class, 'destroy'])
                            ->name('athlete.destroy');
                        Route::delete('athlete/{athleteEnrollment}/force-delete', [AthleteEnrollmentController::class, 'forceDelete'])
                            ->name('athlete.force-delete');
                        Route::post('{athleteEnrollment}/confirm-completion', [AthleteEnrollmentController::class, 'confirmCompletion'])
                            ->name('athlete.confirm-completion');
                        Route::post('athlete/confirm-completion-bulk', [AthleteEnrollmentController::class, 'confirmCompletionBulk'])
                            ->name('athlete.confirm-completion-bulk');

                        // Individual & staff share controller
                        Route::resource('individual', IndividualEnrollmentController::class)->only(['index']);
                        Route::resource('staff', IndividualEnrollmentController::class)->only(['index']);

                        Route::delete('individual/{individualEnrollment}', [IndividualEnrollmentController::class, 'destroy'])
                            ->name('individual.destroy');
                        Route::patch('individual/{individualEnrollment}/status', [IndividualEnrollmentController::class, 'updateStatus'])
                            ->name('individual.update-status');

                        // Coach
                        Route::resource('coach', CoachEnrollmentController::class)
                            ->only(['index', 'destroy'])
                            ->parameters(['coach' => 'coach_enrollment']);
                        Route::get('coach/registered', [CoachEnrollmentController::class, 'registered'])
                            ->name('coach.registered');
                        Route::delete('coach/{coach_enrollment}/force-delete', [CoachEnrollmentController::class, 'forceDelete'])
                            ->name('coach.force-delete');

                        /* Enrollment exports */
                        Route::post('individual/export', [IndividualEnrollmentController::class, 'export'])
                            ->middleware('throttle:3,1')
                            ->name('individual.export');
                        Route::post('athlete/export', [AthleteEnrollmentController::class, 'export'])
                            ->middleware('throttle:3,1')
                            ->name('athlete.export');
                        Route::post('athlete/export-by-discipline', [AthleteEnrollmentController::class, 'exportByDiscipline'])
                            ->middleware('throttle:6,1')
                            ->name('athlete.export-by-discipline');
                        Route::post('coach/export', [CoachEnrollmentController::class, 'export'])
                            ->middleware('throttle:3,1')
                            ->name('coach.export');
                        Route::post('staff/export', [StaffEnrollmentController::class, 'export'])
                            ->middleware('throttle:3,1')
                            ->name('staff.export');
                    });

                    // Competitions / export
                    Route::resource('competitions', CompetitionController::class)->only(['edit', 'update']);
                    Route::resource('export', EventExportController::class)->only(['store'])
                        ->middleware('throttle:3,1');
                });

                /* Event sport ------------------------------------ */
                Route::resource('sport', SportController::class)->except(['show']);

                /* Event hero images ----------------------------- */
                Route::get('event-images', fn () => view('web.admin.evt_events.images.index'))
                    ->name('event-images.index');
            });

        /* -------------------------------------------------------------------- */
        /*  Geographic Management */
        /* -------------------------------------------------------------------- */
        Route::middleware('permission:access settings')->group(function () {

            // Districts
            Route::controller(DistrictController::class)
                ->prefix('districts')
                ->name('districts.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{district}', 'show')->name('show');
                    Route::get('{district}/edit', 'edit')->name('edit');
                    Route::put('{district}', 'update')->name('update');
                    Route::delete('{district}', 'destroy')->name('destroy');
                    Route::patch('{district}/toggle-status', 'toggleStatus')->name('toggle-status');
                });

            // Home page settings
            Route::controller(HomePageSettingsController::class)
                ->prefix('homepage-settings')
                ->name('homepage-settings.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::put('/', 'update')->name('update');
                });

            // Zones
            Route::controller(ZoneController::class)
                ->prefix('zones')
                ->name('zones.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{zone}', 'show')->name('show');
                    Route::get('{zone}/edit', 'edit')->name('edit');
                    Route::put('{zone}', 'update')->name('update');
                    Route::delete('{zone}', 'destroy')->name('destroy');
                    Route::patch('{zone}/toggle-status', 'toggleStatus')->name('toggle-status');
                    Route::get('{zone}/manage-districts', 'manageDistricts')->name('manage-districts');
                    Route::put('{zone}/update-districts', 'updateDistricts')->name('update-districts');
                });
        });

        /* -------------------------------------------------------------------- */
        /*  Operations Center */
        /* -------------------------------------------------------------------- */
        Route::prefix('operations')
            ->name('operations.')
            ->middleware('permission:access settings')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\OperationsCenterController::class, 'index'])
                    ->name('index');
                Route::get('/queue', [\App\Http\Controllers\Admin\OperationsCenterController::class, 'queue'])
                    ->name('queue');
                Route::get('/scheduler', [\App\Http\Controllers\Admin\OperationsCenterController::class, 'scheduler'])
                    ->name('scheduler');
                Route::get('/commands', [\App\Http\Controllers\Admin\OperationsCenterController::class, 'commands'])
                    ->name('commands');
                Route::get('/batches', [\App\Http\Controllers\Admin\OperationsCenterController::class, 'batches'])
                    ->name('batches');
            });

        /* -------------------------------------------------------------------- */
        /*  Database Backups */
        /* -------------------------------------------------------------------- */
        Route::prefix('backups')
            ->name('backups.')
            ->middleware(['role:admin', 'permission:access backups'])
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\BackupController::class, 'index'])
                    ->name('index');
                Route::get('/{filename}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])
                    ->name('download')
                    ->where('filename', '[a-zA-Z0-9\-\_\.]+');
            });

        /* -------------------------------------------------------------------- */
        /*  Eligibility Diagnostic Center */
        /* -------------------------------------------------------------------- */
        Route::prefix('diagnostics')
            ->name('diagnostics.')
            ->middleware('permission:access settings')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\DiagnosticsController::class, 'index'])
                    ->name('index');
            });

        /* -------------------------------------------------------------------- */
        /*  Application Templates (Admin) */
        /* -------------------------------------------------------------------- */
        Route::prefix('application-templates')->name('application-templates.')->group(function () {
            Route::get('create', [ApplicationManagementController::class, 'createTemplate'])->name('create');
            Route::post('/', [ApplicationManagementController::class, 'storeTemplate'])->name('store');
            Route::get('{application_template}', [ApplicationManagementController::class, 'showTemplate'])->name('show');
            Route::get('{application_template}/edit', [ApplicationManagementController::class, 'editTemplate'])->name('edit');
            Route::put('{application_template}', [ApplicationManagementController::class, 'updateTemplate'])->name('update');
            Route::delete('{application_template}', [ApplicationManagementController::class, 'destroyTemplate'])->name('destroy');
            Route::post('{application_template}/activate', [ApplicationManagementController::class, 'activateTemplate'])->name('activate');
            Route::post('{application_template}/deactivate', [ApplicationManagementController::class, 'deactivateTemplate'])->name('deactivate');
            Route::patch('{application_template}/update-state', [ApplicationManagementController::class, 'updateTemplateState'])->name('update-state');
        });

        /* -------------------------------------------------------------------- */
        /*  Event Applications */
        /* -------------------------------------------------------------------- */
        // Event Applications Management
        Route::get('event-applications/export', [ApplicationManagementController::class, 'export'])
            ->name('event-applications.export');
        Route::resource('event-applications', ApplicationManagementController::class)
            ->only(['index', 'show', 'destroy'])
            ->parameters(['event-applications' => 'application']);
        Route::post('event-applications/{application}/validate', [ApplicationManagementController::class, 'validateApplication'])
            ->name('event-applications.validate');
        Route::post('event-applications/{application}/return', [ApplicationManagementController::class, 'returnForCorrection'])
            ->name('event-applications.return');
        Route::post('event-applications/{application}/approve', [ApplicationManagementController::class, 'approve'])
            ->name('event-applications.approve');
        Route::post('event-applications/{application}/reject', [ApplicationManagementController::class, 'reject'])
            ->name('event-applications.reject');
        Route::post('event-applications/{application}/publish', [ApplicationManagementController::class, 'publish'])
            ->name('event-applications.publish');
        Route::post('event-applications/{application}/comment', [ApplicationManagementController::class, 'addComment'])
            ->name('event-applications.comment');
        Route::delete('event-applications/{application}/comment/{comment}', [ApplicationManagementController::class, 'deleteComment'])
            ->name('event-applications.comment.delete');
        Route::get('event-applications/{application}/download-documents', [ApplicationManagementController::class, 'downloadDocuments'])
            ->name('event-applications.download-documents');
        Route::get('event-applications/{application}/pdf', [ApplicationManagementController::class, 'exportPdf'])
            ->name('event-applications.pdf');

        // Application Documents (Common routes accessible to Admin)
        Route::post('application-documents/upload', [ApplicationDocumentController::class, 'upload'])
            ->name('application-documents.upload');
        Route::get('application-documents/{document}/download', [ApplicationDocumentController::class, 'download'])
            ->name('application-documents.download');
        Route::delete('application-documents/{document}', [ApplicationDocumentController::class, 'destroy'])
            ->name('application-documents.destroy');

    }); // ── end Admin group ─────────────────────────────────────────────────────
