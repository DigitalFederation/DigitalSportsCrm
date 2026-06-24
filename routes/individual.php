<?php

use App\Http\Controllers\Individual\AthleteController as IndividualAthleteController;
use App\Http\Controllers\Individual\CertificationAttributedController as IndividualCertificationAttributedController;
use App\Http\Controllers\Individual\CertificationCardController as IndividualCertificationCardController;
use App\Http\Controllers\Individual\CertificationCardPdfController as IndividualCertificationCardPdfController;
use App\Http\Controllers\Individual\CertificationValidateController as IndividualCertificationValidateController;
use App\Http\Controllers\Individual\CoachController as IndividualCoachController;
use App\Http\Controllers\Individual\DashboardController as IndividualDashboardController;
use App\Http\Controllers\Individual\DivingCertificationsController;
use App\Http\Controllers\Individual\DivingEntitiesController;
use App\Http\Controllers\Individual\DocumentController as IndividualDocumentController;
use App\Http\Controllers\Individual\DocumentPaymentController as IndividualDocumentPaymentController;
use App\Http\Controllers\Individual\EntityController as IndividualEntityController;
use App\Http\Controllers\Individual\FederationController as IndividualFederationController;
use App\Http\Controllers\Individual\FederationRequestController;
use App\Http\Controllers\Individual\InstructorController as IndividualInstructorController;
use App\Http\Controllers\Individual\InternationalCertificationCardController as IndividualInternationalCertificationCardController;
use App\Http\Controllers\Individual\LicenseAttributedController as IndividualLicensesAttributedController;
use App\Http\Controllers\Individual\LicensePurchaseController as IndividualLicensePurchaseController;
use App\Http\Controllers\Individual\MemberInsuranceController;
use App\Http\Controllers\Individual\MemberSubscriptionController;
use App\Http\Controllers\Individual\OfficialDocumentsController as IndividualOfficialDocumentsController;
use App\Http\Controllers\Individual\ProfileController;
use App\Http\Controllers\Individual\SeparatedLicenseAttributedController as IndividualSeparatedLicenseAttributedController;
use App\Http\Controllers\Individual\TechnicalDirectorPositionsController;
use App\Http\Controllers\Shared\InsuranceDocumentController;
use Illuminate\Support\Facades\Route;

// Individual
Route::group(['prefix' => 'individual', 'middleware' => ['user_group:INDIVIDUAL']], function () {

    Route::controller(IndividualDashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('individual.dashboard');
    });

    Route::controller(IndividualLicensesAttributedController::class)->group(function () {
        Route::get('/licenses-attributed', 'index')->name('individual.license-attributed.index');
        Route::get('/license-attributed/create/{type}', 'create')->name('individual.license-attributed.create');
        Route::post('/license-attributed', 'store')->name('individual.license-attributed.store');
        Route::delete('/license-attributed/{license_attributed}', 'destroy')->name('individual.license-attributed.delete');
    });

    Route::controller(IndividualLicensePurchaseController::class)->group(function () {
        // Shared store and success routes
        Route::post('/license-purchase', 'store')->name('individual.license-purchase.store');
        Route::get('/license-purchase/success', 'success')->name('individual.license-purchase.success');

        // Committee-driven license-purchase routes, generated from
        // config/committees.php. Each committee's individual purchase page
        // becomes one named route (`individual.{slug}.index`) handled by the
        // generic show() method.
        foreach (\App\Support\Committees::registrablePurchaseRoutes(['individual']) as $purchase) {
            Route::get("/{$purchase['slug']}", 'show')
                ->defaults('committeeCode', $purchase['code'])
                ->name("individual.{$purchase['slug']}.index");
        }
    });

    // Committee-driven licenses-attributed routes, generated from
    // config/committees.php (one per committee, `individual.{slug}-licenses-attributed.index`).
    Route::controller(IndividualSeparatedLicenseAttributedController::class)->group(function () {
        foreach (\App\Support\Committees::individualAttributedRoutes() as $attributed) {
            Route::get("/{$attributed['slug']}-licenses-attributed", 'show')
                ->defaults('committeeCode', $attributed['code'])
                ->name("individual.{$attributed['slug']}-licenses-attributed.index");
        }
    });

    Route::controller(IndividualCertificationAttributedController::class)->group(function () {
        Route::get('/certifications', 'index')->name('individual.certifications.index');
        Route::get('/certification-attributed', 'index')->name('individual.certification-attributed.index');
        Route::get('/certification-attributed/grid', 'grid')->name('individual.certification-attributed.grid');
        Route::get('/certification-attributed/{id}', 'show')->name('individual.certification-attributed.show');
        Route::post('/certification-attributed/activate', 'activate')->name('individual.certification-attributed.activate');
        Route::post('/certification-attributed/cancel', 'cancel')->name('individual.certification-attributed.cancel');
    });

    Route::controller(IndividualCertificationCardController::class)->group(function () {
        Route::get('/certification-card', 'index')->name('individual.certification-card.index');
        Route::get('certification-card/{id}', 'show')->name('individual.certification-card.show');
        Route::get('/certification-card/{certificationAttributed}/download', 'download')->name('individual.certification-card.download');
    });

    Route::controller(IndividualInternationalCertificationCardController::class)->group(function () {
        Route::get('/international-certification-card', 'index')->name('individual.international-certification-card.index');
        Route::get('/international-certification-card/{id}', 'show')->name('individual.international-certification-card.show');
        Route::get('/international-certification-card/{certificationAttributed}/download', 'download')->name('individual.international-certification-card.download');
    });

    Route::controller(IndividualCertificationCardPdfController::class)->group(function () {
        Route::get('certification-card-pdf/{id}', 'show')->name('individual.certification-card.pdf');
        Route::get('certification-card-pdf/card/{id}', 'card')->name('individual.certification-card.card-pdf');
        Route::get('certification-card-pdf/preview/{id}', 'preview')->name('individual.certification-card.preview');
    });

    Route::controller(IndividualCertificationValidateController::class)->group(function () {
        Route::get('/certification-validate', 'index')->name('individual.certification-validate.index');
        Route::get('/certification-validate/export-excel', 'exportExcel')
            ->name('individual.certification-validate.export-excel');
        Route::get('/certification-validate/export-pdf', 'exportPdf')
            ->name('individual.certification-validate.export-pdf');
        Route::post('/certification-validate/{certificationAttributed}/activate', 'activate')
            ->name('individual.certification-validate.activate');
        Route::post('/certification-validate/{certificationAttributed}/reject', 'reject')
            ->name('individual.certification-validate.reject');
        Route::get('/certification-validate/{certification}', 'show')
            ->name('individual.certification-validate.show');
    });

    Route::controller(IndividualFederationController::class)->group(function () {
        Route::get('/federation', 'index')->name('individual.federation.index');
        Route::get('/federation/{id}', 'show')->name('individual.federation.show');
        Route::delete('/federation/{federationId}', 'destroy')->name('individual.federation.delete');
    });

    Route::controller(IndividualEntityController::class)->group(function () {
        Route::get('/entity', 'index')->name('individual.entity.index');
        Route::get('/entity/{entity}', 'show')->name('individual.entity.show');
        Route::post('/entity/', 'store')->name('individual.entity.store');
        Route::post('/entity-approve/', 'approve')->name('individual.entity.approve');
        Route::delete('/entity/{entity}', 'destroy')->name('individual.entity.delete');
    });

    Route::controller(IndividualInstructorController::class)->group(function () {
        Route::get('/instructor/{committee}', 'index')->name('individual.instructor.index');
        Route::get('/instructor/{committee}/{code}', 'index')->name('individual.instructor-code.index');
        Route::put('/instructor/{id}', 'update')->name('individual.instructor.response');
        Route::delete('/instructor/{id}', 'destroy')->name('individual.instructor.delete');

        Route::get('/instructor-invitations/accept/{entityId}/{userId}/{committeeCode}', 'acceptInvitation')
            ->middleware(['signed'])
            ->name('instructor-invitations.accept');

        Route::get('/instructor-invitations/reject/{entityId}/{userId}/{committeeCode}', 'rejectInvitation')
            ->middleware(['signed'])
            ->name('instructor-invitations.reject');
    });

    Route::controller(IndividualCoachController::class)->group(function () {
        Route::get('/coach', 'index')->name('individual.coach.index');
        Route::put('/coach/{id}', 'response')->name('individual.coach.response');
        Route::delete('/coach/{id}', 'destroy')->name('individual.coach.delete');
    });

    Route::controller(IndividualAthleteController::class)->group(function () {
        Route::get('/athlete', 'index')->name('individual.athlete.index');
        Route::put('/athlete/{id}', 'response')->name('individual.athlete.response');
        Route::delete('/athlete/{entityAthlete}', 'destroy')->name('individual.athlete.delete');
    });

    Route::controller(IndividualOfficialDocumentsController::class)->group(function () {
        Route::get('official-documents/{role}', 'index')->name('individual.official-documents.index');
        Route::get('official-documents/preview/{id}', 'preview')->name('individual.official-documents.preview');
        Route::post('official-documents/{id}', 'download')->name('individual.official-documents.download');
        Route::delete('official-documents/{id}', 'destroy')->name('individual.official-documents.delete');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'show')->name('individual.individual.show');
        Route::get('/profile/edit', 'edit')->name('individual.individual.edit');
        Route::put('/profile/update', 'update')->name('individual.individual.update');
    });

    Route::prefix('attachments')->name('individual.')->group(function () {
        Route::prefix('committee/{committee?}')->name('committee.')->group(function () {
            Route::resource('attachments', \App\Http\Controllers\Individual\AttachmentsController::class)
                ->except(['show', 'edit', 'update']);
        });

        // Routes for no committees (null committee)
        Route::resource('attachments', \App\Http\Controllers\Individual\AttachmentsController::class)
            ->parameters(['attachments' => 'attachment'])
            ->except(['show', 'edit', 'update']);

        // Route download attachment
        Route::post('/attachments/download/{id}', [\App\Http\Controllers\Individual\AttachmentsController::class, 'download'])
            ->name('attachments.download');
    });

    Route::prefix('evt-events')->name('individual.evt-events.')->group(function () {
        // Default route redirects to competitions (sports events are primary)
        Route::redirect('/', 'evt-events/competitions');

        Route::get('/competitions', [\App\Http\Controllers\Individual\EvtEvents\EventsController::class, 'competitionsIndex'])->name('competitions.index');
        Route::get('/organization', [\App\Http\Controllers\Individual\EvtEvents\EventsController::class, 'index'])->name('events.index');
        Route::get('/events/{event}', [\App\Http\Controllers\Individual\EvtEvents\EventsController::class, 'show'])->name('events.show');

        Route::prefix('competition/{competition}')->name('competitions.')->group(function () {
            Route::resource('disciplines', \App\Http\Controllers\Individual\EvtEvents\DisciplineController::class)->only('index');
        });

        Route::resource('enrollments', \App\Http\Controllers\Individual\EvtEvents\Enrollments\IndividualEnrollmentController::class)->only(['store']);

        // media
        Route::prefix('events/{event}')->name('events.')->group(function () {
            Route::resource('download-media', \App\Http\Controllers\Common\DownloadEventMediaController::class)->only(['store']);

            // Waiting list
            Route::get('waiting-list', [\App\Http\Controllers\Individual\EvtEvents\Enrollments\WaitingListController::class, 'index'])->name('waiting-list.index');
            Route::post('waiting-list', [\App\Http\Controllers\Individual\EvtEvents\Enrollments\WaitingListController::class, 'store'])->name('waiting-list.store');
            Route::delete('waiting-list/{enrollmentType}/{id}', [\App\Http\Controllers\Individual\EvtEvents\Enrollments\WaitingListController::class, 'destroy'])->name('waiting-list.destroy');
        });
    });

    Route::controller(IndividualDocumentController::class)->group(function () {
        Route::get('/documents', 'index')->name('individual.document.index');
        Route::get('/document/download/{id}', 'download')->name('individual.document.download');
        Route::get('/document/{id}/moloni-pdf', \App\Http\Controllers\Shared\MoloniDocumentPdfController::class)->name('individual.document.moloni-pdf');
        Route::get('/document/{id}', 'show')->name('individual.document.show');
    });

    Route::post('/document/{id}/pay', [IndividualDocumentPaymentController::class, 'store'])->name('individual.document.pay');

    Route::prefix('subscriptions')->name('individual.subscriptions.')->group(function () {
        Route::get('/', [MemberSubscriptionController::class, 'index'])->name('index');
        Route::get('/create', [MemberSubscriptionController::class, 'create'])->name('create');
        Route::post('/', [MemberSubscriptionController::class, 'store'])->name('store');
        Route::get('/{subscription}', [MemberSubscriptionController::class, 'show'])->name('show');
        Route::post('/{subscription}/renew', [MemberSubscriptionController::class, 'renew'])->name('renew');
        Route::get('/history', [MemberSubscriptionController::class, 'history'])->name('history');
        // Route for subscribing to membership packages (including insurance-only)
        Route::post('/membership-packages/{package}/subscribe', [MemberSubscriptionController::class, 'subscribeToPackage'])->name('membership-packages.subscribe');
    });

    // Federation Association Request Routes
    Route::prefix('federation-request')->name('individual.federation-request.')->group(function () {
        Route::get('/create', [FederationRequestController::class, 'create'])->name('create');
        Route::post('/store', [FederationRequestController::class, 'store'])->name('store');
        Route::get('/success', [FederationRequestController::class, 'success'])->name('success');
    });

    Route::prefix('insurance')->name('individual.insurance.')->group(function () {
        Route::get('/', [MemberInsuranceController::class, 'index'])->name('index');

        // Add routes for the insurance document view and download
        Route::get('/{insurance}/document', [InsuranceDocumentController::class, 'show'])
            ->name('document.show');
        Route::get('/{insurance}/document/download', [InsuranceDocumentController::class, 'download'])
            ->name('document.download');
        Route::get('/{insurance}/conditions/download', [InsuranceDocumentController::class, 'downloadConditions'])
            ->name('conditions.download');
    });

    // Diving Professional Certifications
    Route::prefix('diving-certifications')->name('individual.diving_certifications.')->group(function () {
        Route::get('/', [DivingCertificationsController::class, 'index'])->name('index');
        Route::get('/create', [DivingCertificationsController::class, 'create'])->name('create');
        Route::post('/', [DivingCertificationsController::class, 'store'])->name('store');
        Route::get('/{certification}', [DivingCertificationsController::class, 'show'])->name('show');
        Route::get('/{certification}/edit', [DivingCertificationsController::class, 'edit'])->name('edit');
        Route::put('/{certification}', [DivingCertificationsController::class, 'update'])->name('update');
        Route::delete('/{certification}', [DivingCertificationsController::class, 'destroy'])->name('destroy');
    });

    // Diving Entities
    Route::prefix('diving-entities')->name('individual.diving_entities.')->group(function () {
        Route::get('/', [DivingEntitiesController::class, 'index'])->name('index');
        Route::get('/{entity}', [DivingEntitiesController::class, 'show'])->name('show');
        Route::post('/', [DivingEntitiesController::class, 'store'])->name('store');
        Route::post('/approve', [DivingEntitiesController::class, 'approve'])->name('approve');
        Route::post('/reject', [DivingEntitiesController::class, 'reject'])->name('reject');
        Route::delete('/{entity}', [DivingEntitiesController::class, 'destroy'])->name('destroy');
    });

    // Diving Professionals (Professional Relationships)
    Route::prefix('diving-professionals')->name('individual.diving_professionals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Individual\DivingProfessionalsController::class, 'index'])->name('index');
        Route::post('/{professionalRole}/accept', [\App\Http\Controllers\Individual\DivingProfessionalsController::class, 'accept'])->name('accept');
        Route::post('/{professionalRole}/reject', [\App\Http\Controllers\Individual\DivingProfessionalsController::class, 'reject'])->name('reject');
        Route::delete('/{professionalRole}', [\App\Http\Controllers\Individual\DivingProfessionalsController::class, 'destroy'])->name('destroy');
    });

    // Technical Director Positions (License-based associations)
    Route::prefix('technical-director-positions')->name('individual.technical_director_positions.')->group(function () {
        Route::get('/', [TechnicalDirectorPositionsController::class, 'index'])->name('index');
        Route::post('/{technicalDirector}/approve', [TechnicalDirectorPositionsController::class, 'approve'])->name('approve');
        Route::post('/{technicalDirector}/reject', [TechnicalDirectorPositionsController::class, 'reject'])->name('reject');
    });

    // Coach History - Shows all past events as coach
    Route::get('event-coach-history', [App\Http\Controllers\Individual\EventCoachHistoryController::class, 'index'])
        ->name('individual.event-coach-history.index');

    // Event Role Management - Unified Index (all roles)
    Route::get('event-official-roles', [App\Http\Controllers\Individual\EventOfficialRolesController::class, 'index'])
        ->name('individual.event-official-roles.index');

    // Referee History - Shows all past events and functions performed
    Route::get('referee-history', [App\Http\Controllers\Individual\RefereeHistoryController::class, 'index'])
        ->name('individual.referee-history.index');

    // Event Role Management - Technical Delegate (unified: TD + Chief Judge)
    Route::prefix('technical-delegate')->name('individual.technical-delegate.')->group(function () {
        Route::get('/', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'index'])->name('index');

        // Technical Delegate enrollment routes
        Route::get('/{event}/enrollments', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'showEnrollments'])
            ->name('enrollments')->middleware('event.role:technical_delegate');
        Route::get('/{event}/enrollments/export', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'exportEnrollments'])
            ->name('enrollments.export')->middleware('event.role:technical_delegate');

        // Technical Delegate Report routes
        Route::get('/{event}/td-report', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'tdReport'])
            ->name('td-report')->middleware('event.role:technical_delegate');
        Route::post('/{event}/td-report/save', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'saveTdReport'])
            ->name('td-report.save')->middleware('event.role:technical_delegate');
        Route::post('/{event}/td-report/submit', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'submitTdReport'])
            ->name('td-report.submit')->middleware('event.role:technical_delegate');
        Route::post('/{event}/td-report/document', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'uploadTdDocument'])
            ->name('td-report.upload')->middleware('event.role:technical_delegate');
        Route::delete('/{event}/td-report/document/{document}', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'deleteTdDocument'])
            ->name('td-report.document.delete')->middleware('event.role:technical_delegate');
        Route::get('/{event}/td-report/document/{document}/download', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'downloadTdDocument'])
            ->name('td-report.document.download')->middleware('event.role:technical_delegate');

        // Chief Judge referee management routes
        Route::get('/{event}/referees', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'showReferees'])
            ->name('referees')->middleware('event.role:chief_judge');
        Route::get('/{event}/referees/export', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'exportReferees'])
            ->name('referees.export')->middleware('event.role:chief_judge');
        Route::post('/{event}/assign-function', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'assignFunction'])
            ->name('assign-function')->middleware('event.role:chief_judge');
        Route::delete('/{event}/remove-function/{assignment}', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'removeFunction'])
            ->name('remove-function')->middleware('event.role:chief_judge');
        Route::post('/{event}/presence', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'updatePresence'])
            ->name('presence')->middleware('event.role:chief_judge');

        // Chief Judge Report routes
        Route::get('/{event}/cj-report', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'cjReport'])
            ->name('cj-report')->middleware('event.role:chief_judge');
        Route::post('/{event}/cj-report/save', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'saveCjReport'])
            ->name('cj-report.save')->middleware('event.role:chief_judge');
        Route::post('/{event}/cj-report/submit', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'submitCjReport'])
            ->name('cj-report.submit')->middleware('event.role:chief_judge');
        Route::post('/{event}/cj-report/document', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'uploadCjDocument'])
            ->name('cj-report.upload')->middleware('event.role:chief_judge');
        Route::delete('/{event}/cj-report/document/{document}', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'deleteCjDocument'])
            ->name('cj-report.document.delete')->middleware('event.role:chief_judge');
        Route::get('/{event}/cj-report/document/{document}/download', [App\Http\Controllers\Individual\TechnicalDelegateController::class, 'downloadCjDocument'])
            ->name('cj-report.document.download')->middleware('event.role:chief_judge');
    });

});
