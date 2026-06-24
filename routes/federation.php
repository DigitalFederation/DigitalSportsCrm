<?php

use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\Federation\CertificationAttributedController as FederationCertificationAttributedController;
use App\Http\Controllers\Federation\CertificationCardController as FederationCertificationCardController;
use App\Http\Controllers\Federation\CertificationCardPdfController;
use App\Http\Controllers\Federation\DashboardController as FederationDashboardController;
use App\Http\Controllers\Federation\DocumentController as FederationDocumentController;
use App\Http\Controllers\Federation\DocumentManualPaymentController;
use App\Http\Controllers\Federation\DocumentPaymentController;
use App\Http\Controllers\Federation\EntityController as FederationEntityController;
use App\Http\Controllers\Federation\EntityRequestController as FederationEntityRequestController;
use App\Http\Controllers\Federation\FederationApplicationController;
use App\Http\Controllers\Federation\FederationController;
use App\Http\Controllers\Federation\IndividualController as FederationIndividualController;
use App\Http\Controllers\Federation\IndividualRequestController as FederationIndividualRequestController;
use App\Http\Controllers\Federation\LicenseAttributedController as FederationLicensesAttributedController;
use App\Http\Controllers\Federation\MembershipController as FederationMembershipController;
use App\Http\Controllers\Federation\OfficialDocumentsController as FederationOfficialDocumentsController;
use App\Http\Controllers\Federation\OfficialDocumentsFromIndividualController as FederationIndividualOfficialDocumentsController;
use App\Http\Controllers\Federation\SeparatedDivingLicenseValidationController as FederationSeparatedDivingLicenseValidationController;
use App\Http\Controllers\Federation\SeparatedLicenseAttributedController as FederationSeparatedLicenseAttributedController;
use App\Http\Controllers\Shared\InsuranceDocumentController;
use Illuminate\Support\Facades\Route;

// Federation
Route::group(['prefix' => 'federation', 'middleware' => ['user_group:FEDERATION']], function () {
    // Routes that should always be accessible
    Route::controller(FederationDocumentController::class)->group(function () {
        Route::get('/documents', 'index')->name('federation.document.index');
        Route::get('/document/download/{id}', 'download')->name('federation.document.download');
        Route::get('/document/{id}/moloni-pdf', \App\Http\Controllers\Shared\MoloniDocumentPdfController::class)->name('federation.document.moloni-pdf');
        Route::get('/document/{id}', 'show')->name('federation.document.show');
    });

    Route::post('/document/{id}/pay', [DocumentPaymentController::class, 'store'])->name('federation.document.pay');
    Route::post('/document/{document}/manual-payment', [DocumentManualPaymentController::class, 'store'])->name('federation.document.manual-payment');

    Route::controller(FederationDashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('federation.dashboard');
    });

    // Profile
    Route::get('/profile/edit', [FederationController::class, 'edit'])->name('federation.profile.edit');
    Route::put('/profile/update', [FederationController::class, 'update'])->name('federation.profile.update');

    // Event Applications
    Route::get('event-applications/available-templates', [FederationApplicationController::class, 'availableTemplates'])
        ->name('federation.event-applications.available-templates');
    Route::get('event-applications/create/template/{template}', [FederationApplicationController::class, 'createFromTemplate'])
        ->name('federation.event-applications.create-from-template');
    Route::get('event-applications/create/direct', [FederationApplicationController::class, 'createDirect'])
        ->name('federation.event-applications.create-direct');
    Route::post('event-applications/{application}/submit', [FederationApplicationController::class, 'submit'])
        ->name('federation.event-applications.submit');
    Route::resource('event-applications', FederationApplicationController::class)
        ->names('federation.event-applications')
        ->parameters(['event-applications' => 'application'])
        ->except(['create']); // Uses create-from-template and create-direct instead

    // Event Application Management (state transitions, comments, exports)
    Route::get('event-applications/export', [FederationApplicationController::class, 'export'])
        ->name('federation.event-applications.export');
    Route::post('event-applications/{application}/validate', [FederationApplicationController::class, 'validateApplication'])
        ->name('federation.event-applications.validate');
    Route::post('event-applications/{application}/return', [FederationApplicationController::class, 'returnForCorrection'])
        ->name('federation.event-applications.return');
    Route::post('event-applications/{application}/approve', [FederationApplicationController::class, 'approve'])
        ->name('federation.event-applications.approve');
    Route::post('event-applications/{application}/reject', [FederationApplicationController::class, 'reject'])
        ->name('federation.event-applications.reject');
    Route::post('event-applications/{application}/publish', [FederationApplicationController::class, 'publish'])
        ->name('federation.event-applications.publish');
    Route::post('event-applications/{application}/comment', [FederationApplicationController::class, 'addComment'])
        ->name('federation.event-applications.comment');
    Route::delete('event-applications/{application}/comment/{comment}', [FederationApplicationController::class, 'deleteComment'])
        ->name('federation.event-applications.comment.delete');
    Route::get('event-applications/{application}/download-documents', [FederationApplicationController::class, 'downloadDocuments'])
        ->name('federation.event-applications.download-documents');
    Route::get('event-applications/{application}/pdf', [FederationApplicationController::class, 'exportPdf'])
        ->name('federation.event-applications.pdf');

    // Application Documents
    Route::post('application-documents/upload', [ApplicationDocumentController::class, 'upload'])
        ->name('federation.application-documents.upload');
    Route::get('application-documents/{document}/download', [ApplicationDocumentController::class, 'download'])
        ->name('federation.application-documents.download');
    Route::delete('application-documents/{document}', [ApplicationDocumentController::class, 'destroy'])
        ->name('federation.application-documents.destroy');

    // Application Templates (Main Federation Only)
    Route::resource('application-templates', \App\Http\Controllers\Federation\FederationTemplateController::class)
        ->names('federation.application-templates');
    Route::post('application-templates/{application_template}/activate', [\App\Http\Controllers\Federation\FederationTemplateController::class, 'activate'])
        ->name('federation.application-templates.activate');
    Route::post('application-templates/{application_template}/deactivate', [\App\Http\Controllers\Federation\FederationTemplateController::class, 'deactivate'])
        ->name('federation.application-templates.deactivate');
    Route::patch('application-templates/{application_template}/update-state', [\App\Http\Controllers\Federation\FederationTemplateController::class, 'updateState'])
        ->name('federation.application-templates.update-state');

    Route::controller(FederationIndividualOfficialDocumentsController::class)->middleware('permission:access federation official documents')->group(function () {
        Route::get('/official-documents', 'index')->name('federation.official-documents.index');
        Route::get('/official-documents/create', 'create')->name('federation.official-documents.create');

        Route::put('/official-documents/{document}/activate', 'activate')->name('federation.official-documents.activate');
        Route::put('/official-documents/{document}/reject', 'reject')->name('federation.official-documents.reject');
        Route::put('/official-documents/{document}', 'update')->name('federation.official-documents.update');
        Route::post('/official-documents/{document}', 'download')->name('federation.official-documents.download');
        Route::delete('/official-documents/{document}', 'destroy')->name('federation.official-documents.delete');
        Route::put('/official-documents/{id}/update-dates', 'updateDates')->name('federation.official-documents.update-dates');
    });

    Route::controller(FederationOfficialDocumentsController::class)->group(function () {
        Route::get('my-official-documents', 'index')->name('federation.my-official-documents.index');
        Route::post('my-official-documents/{id}', 'download')->name('federation.my-official-documents.download');
        Route::delete('my-official-documents/{id}', 'destroy')->name('federation.my-official-documents.delete');
    });

    // TODO: Add permissions
    Route::controller(FederationLicensesAttributedController::class)->group(function () {
        Route::get('/licenses-attributed', 'index')->name('federation.license-attributed.index');
        Route::get('/license-attributed/create/{license_type_name}/{committee}', 'create')->name('federation.license-attributed.create');
        Route::post('/license-attributed', 'store')->name('federation.license-attributed.store');
        Route::get('/license-attributed/{id}', 'show')->name('federation.license-attributed.show');
        Route::get('/license-attributed/{id}/pdf', [\App\Http\Controllers\Federation\DivingLicensePdfController::class, 'show'])
            ->name('federation.license-attributed.pdf')
            ->middleware('throttle:10,1');
        Route::delete('/license-attributed/{id}', 'destroy')->name('federation.license-attributed.delete');
        Route::put('/license-attributed/{id}/activate', 'activate')->name('federation.license-attributed.activate');
        Route::put('/license-attributed/{id}/approve', 'approve')->name('federation.license-attributed.approve');
        Route::put('/license-attributed/{id}/cancel', 'cancel')->name('federation.license-attributed.cancel');
        Route::put('/license-attributed/provisional', 'provisional')->name('federation.license-attributed.provisional');
    });

    // Suspend License
    Route::name('federation.')->group(function () {
        Route::resource('license-suspend', \App\Http\Controllers\Federation\SuspendLicenseAttributedController::class)->only(['store']);
    });

    // Separated License Attributed Routes (by committee + international + holder type)
    // Committee-driven licenses-attributed routes, generated from
    // config/committees.php: `federation.{slug}-{holder}-licenses-attributed.index`
    // for each committee and holder (entity/individual).
    Route::controller(FederationSeparatedLicenseAttributedController::class)->group(function () {
        foreach (\App\Support\Committees::holderAttributedRoutes() as $attributed) {
            Route::get("/{$attributed['slug']}-{$attributed['holder']}-licenses-attributed", 'show')
                ->defaults('committeeCode', $attributed['code'])
                ->defaults('holder', $attributed['holder'])
                ->name("federation.{$attributed['slug']}-{$attributed['holder']}-licenses-attributed.index");
        }
    });

    // Separated Diving License Validation (Main Federation Only)
    // Entity Diving License Validation
    Route::prefix('entity-diving-license-validation')->name('federation.entity_diving_license_validation.')->group(function () {
        Route::get('/', [FederationSeparatedDivingLicenseValidationController::class, 'entityIndex'])->name('index');
        Route::get('{licenseAttributed}', [FederationSeparatedDivingLicenseValidationController::class, 'entityShow'])->name('show');
        Route::get('{licenseAttributed}/pdf', [\App\Http\Controllers\Federation\DivingLicensePdfController::class, 'show'])
            ->name('pdf')->middleware('throttle:10,1');
        Route::post('{licenseAttributed}/approve', [FederationSeparatedDivingLicenseValidationController::class, 'entityApprove'])->name('approve');
        Route::post('{licenseAttributed}/reject', [FederationSeparatedDivingLicenseValidationController::class, 'entityReject'])->name('reject');
        Route::delete('{licenseAttributed}', [FederationSeparatedDivingLicenseValidationController::class, 'entityDestroy'])->name('destroy');
    });

    // Individual Diving License Validation
    Route::prefix('individual-diving-license-validation')->name('federation.individual_diving_license_validation.')->group(function () {
        Route::get('/', [FederationSeparatedDivingLicenseValidationController::class, 'individualIndex'])->name('index');
        Route::get('{licenseAttributed}', [FederationSeparatedDivingLicenseValidationController::class, 'individualShow'])->name('show');
        Route::get('{licenseAttributed}/pdf', [\App\Http\Controllers\Federation\DivingLicensePdfController::class, 'show'])
            ->name('pdf')->middleware('throttle:10,1');
        Route::post('{licenseAttributed}/approve', [FederationSeparatedDivingLicenseValidationController::class, 'individualApprove'])->name('approve');
        Route::post('{licenseAttributed}/reject', [FederationSeparatedDivingLicenseValidationController::class, 'individualReject'])->name('reject');
        Route::delete('{licenseAttributed}', [FederationSeparatedDivingLicenseValidationController::class, 'individualDestroy'])->name('destroy');
    });

    Route::controller(FederationCertificationAttributedController::class)->group(function () {
        // Routes requiring certification issuance permission
        Route::middleware('federation.can_issue_certifications')->group(function () {
            Route::get('/certifications-attributed', 'index')->name('federation.certification-attributed.index');
            Route::post('/certification-attributed/activate', 'activate')->name('federation.certification-attributed.activate');
            Route::post('/certification-attributed/suspend', 'suspend')->name('federation.certification-attributed.suspend');
            Route::post('/certification-attributed/unsuspend', 'unsuspend')->name('federation.certification-attributed.unsuspend');
            Route::post('/certification-attributed/cancel', 'cancel')->name('federation.certification-attributed.cancel');
            Route::get('/certification-attributed/create', 'create')->name('federation.certification-attributed.create');
            Route::post('/certification-attributed', 'store')->name('federation.certification-attributed.store');
            Route::get('/certification-attributed/{certification}/edit', 'edit')->name('federation.certification-attributed.edit');
            Route::put('/certification-attributed/{certification_attributed}', 'update')->name('federation.certification-attributed.update');
            Route::delete('/certification-attributed/{certification_attributed}', 'destroy')->name('federation.certification-attributed.delete');

            // New Wizard Route
            Route::get('/certification-attributed/wizard/create', 'createWizard')->name('federation.certification-attributed.wizard.create');
        });

        // Show route accessible to all federations (can view in Individual profiles)
        Route::get('/certification-attributed/{certification}', 'show')->name('federation.certification-attributed.show');
    });

    Route::controller(FederationCertificationCardController::class)->group(function () {
        Route::get('/certification-card/{certificationAttributed}/download', 'download')->name('federation.certification-card.download');
    });

    Route::controller(CertificationCardPdfController::class)->group(function () {
        Route::get('certification-card-pdf/card/{id}', 'card')->name('federation.certification-card.card-pdf');
    });

    Route::controller(FederationIndividualController::class)->group(function () {
        Route::get('/individuals', 'index')->name('federation.individual.index');
        Route::get('/individuals/create', 'create')->name('federation.individual.create');
        Route::get('/individual/{individual}', 'show')->name('federation.individual.show');
        Route::get('/individual/{individual}/files', 'files')->name('federation.individual.files');
        Route::get('/individual/{individual}/edit', 'edit')->name('federation.individual.edit');
        Route::put('/individual/{individual}', 'update')->name('federation.individual.update');
        Route::post('/individuals', 'store')->name('federation.individual.store');
        Route::get('/individual/{individual}/update-email', 'showUpdateEmailForm')->name('federation.individual.show-update-email');
        Route::put('/individual/{individual}/update-email', 'updateEmail')->name('federation.individual.update-email');
        Route::delete('/individual/{individual}', 'destroy')->name('federation.individual.delete');
    });

    Route::controller(FederationIndividualRequestController::class)->group(function () {
        Route::get('/individual-requests', 'index')->name('federation.individual-request.index');
        Route::post('/individual-request/reject/{id}', 'reject')->name('federation.individual-request.reject');
        Route::post('/individual-request/accept/{id}', 'accept')->name('federation.individual-request.accept');
        Route::delete('/individual-request/{id}', 'destroy')->name('federation.individual-request.delete');
    });

    Route::controller(FederationEntityController::class)->group(function () {
        Route::get('/entities', 'index')->name('federation.entity.index');
        Route::get('/entity/create', 'create')
            ->name('federation.entity.create')
            ->middleware('permission:create entities');
        Route::post('/entity', 'store')
            ->name('federation.entity.store')
            ->middleware('permission:create entities');
        Route::get('/entity/{entity}', 'show')->name('federation.entity.show');
        Route::get('/entity/{entity}/edit', 'edit')->name('federation.entity.edit');
        Route::put('/entity/{entity}', 'update')->name('federation.entity.update');
        Route::delete('/entity/{entity}', 'destroy')->name('federation.entity.delete');
    });

    Route::controller(FederationEntityRequestController::class)->group(function () {
        Route::get('/entity-requests', 'index')->name('federation.entity-request.index');
        Route::post('/entity-request/accept/{entity}', 'accept')->name('federation.entity-request.accept');
        Route::post('/entity-request/reject/{id}', 'reject')->name('federation.entity-request.reject');
    });

    // Membership controller only has index() implemented
    Route::controller(FederationMembershipController::class)->group(function () {
        Route::get('/memberships', 'index')->name('federation.membership.index');
    });

    Route::name('federation.')->group(function () {
        Route::resource('local-membership-plan', \App\Http\Controllers\Federation\LocalMembershipPlanController::class)
            ->except(['show']); // Controller has no show() method
    });

    // Individual Insurance Management (Federation facilitating for Entities)
    Route::prefix('individual-insurances')->name('federation.individual-insurances.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Federation\IndividualInsuranceController::class, 'index'])->name('index');
        Route::get('/{insurance}', [\App\Http\Controllers\Federation\IndividualInsuranceController::class, 'show'])->name('show');
    });

    // Insurance Documents (using shared controller)
    Route::prefix('insurances/{insurance}/document')->name('federation.insurances.document.')->group(function () {
        Route::get('/', [InsuranceDocumentController::class, 'show'])->name('show');
        Route::get('/download', [InsuranceDocumentController::class, 'download'])->name('download');
        Route::get('/conditions', [InsuranceDocumentController::class, 'downloadConditions'])->name('conditions');
    });

    // Individual Membership Management (DEPRECATED - Use individual-affiliations instead)
    // Route::prefix('individual-memberships')->name('federation.individual-memberships.')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\Federation\IndividualMembershipController::class, 'index'])->name('index');
    //     Route::get('/create', [\App\Http\Controllers\Federation\IndividualMembershipController::class, 'create'])->name('create');
    //     Route::get('/{subscription}', [\App\Http\Controllers\Federation\IndividualMembershipController::class, 'show'])->name('show');
    // });

    // Individual Affiliations (Federation managing individual affiliations)
    Route::prefix('individual-affiliations')->name('federation.individual-affiliations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Federation\IndividualAffiliationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Federation\IndividualAffiliationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Federation\IndividualAffiliationController::class, 'store'])->name('store');
        Route::get('/{affiliation}', [\App\Http\Controllers\Federation\IndividualAffiliationController::class, 'show'])->name('show');
    });

    // Entity Subscriptions (DEPRECATED - Use entity-affiliations instead)
    // Route::prefix('entity-subscriptions')->name('federation.entity-subscriptions.')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\Federation\SubscriptionController::class, 'index'])->name('index');
    //     Route::get('/create', [\App\Http\Controllers\Federation\SubscriptionController::class, 'create'])->name('create');
    //     Route::post('/', [\App\Http\Controllers\Federation\SubscriptionController::class, 'store'])->name('store');
    //     Route::get('/{subscription}', [\App\Http\Controllers\Federation\SubscriptionController::class, 'show'])->name('show');
    //     Route::put('/{subscription}', [\App\Http\Controllers\Federation\SubscriptionController::class, 'update'])->name('update');
    // });

    // Entity Insurances (Federation helping Entities with insurance-only packages)
    Route::prefix('entity-insurances')->name('federation.entity-insurances.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Federation\InsuranceController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Federation\InsuranceController::class, 'create'])->name('create');
        Route::get('/{insurance}', [\App\Http\Controllers\Federation\InsuranceController::class, 'show'])->name('show');
        Route::post('/', [\App\Http\Controllers\Federation\InsuranceController::class, 'store'])->name('store');
    });

    // Entity Affiliations (Federation managing Entity affiliations and packages)
    Route::prefix('entity-affiliations')->name('federation.entity-affiliations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Federation\AffiliationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Federation\AffiliationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Federation\AffiliationController::class, 'store'])->name('store');
        Route::get('/{affiliation}', [\App\Http\Controllers\Federation\AffiliationController::class, 'show'])->name('show');
    });

    /**
     * Events
     */
    Route::prefix('evt-events')->name('federation.evt-events.')->group(function () {

        // Event management routes for the main federation only.
        Route::middleware(['ensureIsDefaultFederation', 'permission:manage-events'])->group(function () {
            Route::get('events/create/{category?}', [\App\Http\Controllers\Federation\EvtEvents\EventsController::class, 'create'])
                ->name('events.create');
            Route::post('events', [\App\Http\Controllers\Federation\EvtEvents\EventsController::class, 'store'])
                ->name('events.store');
            Route::delete('events/{event}', [\App\Http\Controllers\Federation\EvtEvents\EventsController::class, 'destroy'])
                ->name('events.destroy');
            Route::get('events/export', [\App\Http\Controllers\Federation\EvtEvents\EventsController::class, 'masterexport'])
                ->middleware('throttle:3,1')
                ->name('events.export');
        });

        Route::resource('/events', \App\Http\Controllers\Federation\EvtEvents\EventsController::class)->only(['index', 'show', 'edit', 'update']);

        Route::prefix('events/{event}')->name('events.')->group(function () {

            Route::resource('download-media', \App\Http\Controllers\Common\DownloadEventMediaController::class)->only(['store']);
            Route::resource('individual-enrollment', \App\Http\Controllers\Federation\EvtEvents\Enrollments\IndividualEnrollmentController::class)->only(['index', 'create']);
            Route::get('individual-enrollment/export', [\App\Http\Controllers\Federation\EvtEvents\Enrollments\IndividualEnrollmentController::class, 'export'])
                ->name('individual-enrollment.export');
            Route::delete('individual-enrollment/{individualEnrollment}', [\App\Http\Controllers\Federation\EvtEvents\Enrollments\IndividualEnrollmentController::class, 'destroy'])
                ->name('individual-enrollment.destroy');

            Route::get('athlete-enrollment/registered', [\App\Http\Controllers\Federation\EvtEvents\Enrollments\AthleteEnrollmentController::class, 'registered'])
                ->name('athlete-enrollment.registered');
            Route::resource('athlete-enrollment', \App\Http\Controllers\Federation\EvtEvents\Enrollments\AthleteEnrollmentController::class)->only(['index', 'store']);
            Route::get('athlete-enrollment/public', [\App\Http\Controllers\Federation\EvtEvents\Enrollments\AthleteEnrollmentController::class, 'publicIndex'])
                ->name('athlete-enrollment.public');

            Route::delete('athlete-enrollment/{athleteEnrollment}', [\App\Http\Controllers\Federation\EvtEvents\Enrollments\AthleteEnrollmentController::class, 'destroy'])
                ->name('athlete-enrollment.destroy');

            Route::resource('staff-enrollment', \App\Http\Controllers\Federation\EvtEvents\Enrollments\StaffEnrollmentController::class)->only(['index', 'create', 'store', 'destroy']);
            Route::get('coach-enrollment/registered', [\App\Http\Controllers\Federation\EvtEvents\Enrollments\CoachEnrollmentController::class, 'registered'])
                ->name('coach-enrollment.registered');
            Route::resource('coach-enrollment', \App\Http\Controllers\Federation\EvtEvents\Enrollments\CoachEnrollmentController::class)->only(['index', 'destroy']);
            Route::get('officials-enrollment/registered', [\App\Http\Controllers\Federation\EvtEvents\Enrollments\TeamOfficialEnrollmentController::class, 'registered'])
                ->name('officials-enrollment.registered');
            Route::resource('officials-enrollment', \App\Http\Controllers\Federation\EvtEvents\Enrollments\TeamOfficialEnrollmentController::class)->only(['index', 'destroy']);

            Route::middleware(['ensureIsDefaultFederation'])->group(function () {
                Route::resource('referee-enrollment', \App\Http\Controllers\Federation\EvtEvents\Enrollments\RefereeEnrollmentController::class)->only(['index', 'create', 'destroy']);
            });

            Route::get('/enrollment/{type}', [\App\Http\Controllers\Federation\EvtEvents\EnrollmentsController::class, 'create'])
                ->name('enrollments.create');

            Route::get('/registration/', [\App\Http\Controllers\Federation\EvtEvents\RegistrationController::class, 'create'])
                ->name('enrollments.pre-register');

            // Step 2: Review & Pay
            Route::get('/review/', [\App\Http\Controllers\Federation\EvtEvents\ReviewController::class, 'show'])
                ->name('review');

            // Step 3: Confirmed Enrollments
            Route::get('/confirmed-enrollments/', [\App\Http\Controllers\Federation\EvtEvents\ConfirmedEnrollmentsController::class, 'show'])
                ->name('confirmed-enrollments');

            Route::get('/organizer-enrollments/{enrollmentType}', [\App\Http\Controllers\Federation\EvtEvents\Enrollments\OrganizerEnrollmentsController::class, 'index'])
                ->name('organizer-enrollments.index');
            Route::post('/organizer-enrollments/{enrollmentType}/export', [\App\Http\Controllers\Federation\EvtEvents\Enrollments\OrganizerEnrollmentsController::class, 'export'])
                ->name('organizer-enrollments.export');

            // Enrolled Lists
            Route::get('/overview/athletes', [\App\Http\Controllers\Federation\EvtEvents\EventsController::class, 'athletesOverview'])
                ->name('overview.athletes');
        });

        Route::prefix('competition/{competition}')->name('competitions.')->group(function () {
            Route::resource('disciplines', \App\Http\Controllers\Federation\EvtEvents\DisciplineController::class)->only('index');
        });
    });

    Route::prefix('attachments')->name('federation.')->group(function () {
        Route::prefix('committee/{committee?}')->name('committee.')->group(function () {
            Route::resource('attachments', \App\Http\Controllers\Federation\AttachmentsController::class)
                ->except(['show', 'edit', 'update', 'create']);
        });

        // Routes for no committees (null committee)
        Route::resource('attachments', \App\Http\Controllers\Federation\AttachmentsController::class)
            ->parameters(['attachments' => 'attachment'])
            ->only(['index', 'destroy']);

        // Route download attachment
        Route::post('/attachments/download/{id}', [\App\Http\Controllers\Federation\AttachmentsController::class, 'download'])
            ->name('attachments.download');

        Route::prefix('committee/{committee?}')->name('committee.')->group(function () {
            Route::resource('attachments-sent', \App\Http\Controllers\Federation\AttachmentsSentController::class)
                ->except(['show', 'edit', 'update']);
        });

        // Routes for no committees (null committee)
        Route::resource('attachments-sent', \App\Http\Controllers\Federation\AttachmentsSentController::class)
            ->parameters(['attachments' => 'attachment'])
            ->except(['show', 'edit', 'update']);
    });

    /**
     * Eligibility Diagnostic Center
     */
    Route::get('/diagnostics', [\App\Http\Controllers\Federation\DiagnosticsController::class, 'index'])
        ->name('federation.diagnostics.index');

    /**
     * Exports
     */
    Route::prefix('exports')->name('federation.export.')->group(function () {
        Route::post('/individuals', [\App\Http\Controllers\Federation\Exports\IndividualExportController::class, 'store'])->name('individuals');
    });
});
