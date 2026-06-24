<?php

/**
 * International Routes
 * --------------------------------------------------------------------------
 * All routes in this file handle INTERNATIONAL licenses and certifications
 * (where is_international = true).
 *
 * Every route lives under:
 *      • prefix  : /international
 *      • name    : international.*
 *      • permission: access international licenses
 *
 * This namespace is completely separated from national routes to prevent
 * any mixing of national and international content.
 */

use App\Http\Controllers\International\Entity\CertificationAttributedController as InternationalEntityCertificationAttributedController;
use App\Http\Controllers\International\Entity\LicenseAttributedController as InternationalEntityLicenseAttributedController;
use App\Http\Controllers\International\Entity\LicensePurchaseController as InternationalEntityLicensePurchaseController;
use App\Http\Controllers\International\Federation\CertificationAttributedController as InternationalFederationCertificationAttributedController;
use App\Http\Controllers\International\Federation\LicenseAttributedController as InternationalFederationLicenseAttributedController;
use App\Http\Controllers\International\Individual\CertificationAttributedController as InternationalIndividualCertificationAttributedController;
use App\Http\Controllers\International\Individual\CertificationCardController as InternationalIndividualCertificationCardController;
use App\Http\Controllers\International\Individual\LicenseAttributedController as InternationalIndividualLicenseAttributedController;
use App\Http\Controllers\International\Individual\LicensePurchaseController as InternationalIndividualLicensePurchaseController;
use Illuminate\Support\Facades\Route;

/* ------------------------------------------------------------------------ */
/*  Base International group - All international content */
/* ------------------------------------------------------------------------ */

Route::prefix('international')
    ->name('international.')
    ->middleware(['permission:access international licenses'])
    ->group(function () {

        /* -------------------------------------------------------------------- */
        /*  INDIVIDUAL Namespace - International licenses & certifications */
        /* -------------------------------------------------------------------- */

        Route::prefix('individual')
            ->name('individual.')
            ->middleware(['user_group:INDIVIDUAL'])
            ->group(function () {

                // Licenses Attributed (International only)
                Route::controller(InternationalIndividualLicenseAttributedController::class)
                    ->prefix('licenses-attributed')
                    ->name('licenses-attributed.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/{id}', 'show')->name('show');
                        Route::delete('/{license_attributed}', 'destroy')->name('delete');
                    });

                // License Purchase (International only)
                Route::controller(InternationalIndividualLicensePurchaseController::class)
                    ->prefix('license-purchase')
                    ->name('license-purchase.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/', 'store')->middleware('throttle:10,1')->name('store');
                        Route::get('/success', 'success')->name('success');
                    });

                // Certifications Attributed (International only)
                Route::controller(InternationalIndividualCertificationAttributedController::class)
                    ->prefix('certifications')
                    ->name('certifications.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/{id}', 'show')->name('show');
                    });

                // Certification Cards (Diving + Scientific)
                Route::controller(InternationalIndividualCertificationCardController::class)
                    ->prefix('certification-card')
                    ->name('certification-card.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/{id}', 'show')->name('show');
                        Route::get('/{certificationAttributed}/download', 'download')->name('download');
                    });
            });

        /* -------------------------------------------------------------------- */
        /*  ENTITY Namespace - International licenses & certifications */
        /* -------------------------------------------------------------------- */

        Route::prefix('entity')
            ->name('entity.')
            ->middleware(['user_group:ENTITY'])
            ->group(function () {

                // Licenses Attributed (International only)
                Route::controller(InternationalEntityLicenseAttributedController::class)
                    ->prefix('licenses-attributed')
                    ->name('licenses-attributed.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/{id}', 'show')->name('show');
                    });

                // License Purchase (International only)
                Route::controller(InternationalEntityLicensePurchaseController::class)
                    ->prefix('license-purchase')
                    ->name('license-purchase.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/', 'store')->middleware('throttle:10,1')->name('store');
                        Route::get('/success', 'success')->name('success');
                    });

                // Certifications Attributed (International only)
                Route::controller(InternationalEntityCertificationAttributedController::class)
                    ->prefix('certifications')
                    ->name('certifications.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/{id}', 'show')->name('show');
                    });

                // Member licenses - View licenses attributed to entity's members
                Route::controller(InternationalEntityLicenseAttributedController::class)
                    ->prefix('member-licenses')
                    ->name('member-licenses.')
                    ->group(function () {
                        Route::get('/', 'individuals')->name('index');
                    });
            });

        /* -------------------------------------------------------------------- */
        /*  FEDERATION Namespace - International license & certification management */
        /* -------------------------------------------------------------------- */

        Route::prefix('federation')
            ->name('federation.')
            ->middleware(['user_group:FEDERATION,ADMIN'])
            ->group(function () {

                // Licenses Attributed (International only)
                Route::controller(InternationalFederationLicenseAttributedController::class)
                    ->prefix('licenses-attributed')
                    ->name('licenses-attributed.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/create/{type}/{committee}', 'create')->name('create');
                        Route::post('/', 'store')->middleware('throttle:6,1')->name('store');
                        Route::get('/{id}', 'show')->name('show');
                        Route::put('/{id}/activate', 'activate')->middleware('throttle:6,1')->name('activate');
                        Route::put('/{id}/cancel', 'cancel')->middleware('throttle:6,1')->name('cancel');
                        Route::put('/{id}/approve', 'approve')->middleware('throttle:6,1')->name('approve');
                        Route::delete('/{id}', 'destroy')->name('delete');
                    });

                // Certifications Attributed (International only)
                Route::controller(InternationalFederationCertificationAttributedController::class)
                    ->prefix('certifications-attributed')
                    ->name('certifications-attributed.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/{id}', 'show')->name('show');
                        Route::post('/activate', 'activate')->middleware('throttle:6,1')->name('activate');
                        Route::post('/suspend', 'suspend')->middleware('throttle:6,1')->name('suspend');
                        Route::post('/cancel', 'cancel')->middleware('throttle:6,1')->name('cancel');
                    });
            });
    }); // ── end International group ────────────────────────────────────────────────────
