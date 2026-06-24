<?php

use App\Http\Controllers\AntiDopingController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\IndividualController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

// Public

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// VitePress Documentation (static files)
Route::get('/docs/{path?}', function ($path = 'index.html') {
    $filePath = public_path('docs/' . $path);

    // If path is a directory, serve index.html
    if (is_dir($filePath)) {
        $filePath = rtrim($filePath, '/') . '/index.html';
    }

    // If no extension, try .html
    if (! pathinfo($filePath, PATHINFO_EXTENSION)) {
        $filePath .= '.html';
    }

    if (! file_exists($filePath)) {
        abort(404);
    }

    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'woff2' => 'font/woff2',
        'woff' => 'font/woff',
        'ttf' => 'font/ttf',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'ico' => 'image/x-icon',
    ];

    return response()->file($filePath, [
        'Content-Type' => $mimeTypes[$extension] ?? 'application/octet-stream',
    ]);
})->where('path', '.*');

// Impersonate STOP
Route::get('impersonate/stop', [App\Http\Controllers\Admin\ImpersonateController::class, 'stop'])
    ->name('impersonate.stop');

Route::get('/impersonate-switch', function () {
    // This route is intentionally empty
    // It serves only as a URL endpoint to force a new request cycle
    // All the actual logic is handled in the middleware
})->middleware('web');

Route::get('email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['custom-signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::get('/email/verify', function (Request $request) {
    if ($request->user() && $request->user()->hasVerifiedEmail()) {
        return redirect('/dashboard');
    }

    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/* ----- */

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect('/dashboard');
    } else {
        // return view('auth.login');
        return view('web.welcome');
    }
});

// Routes for legal documents
Route::get('terms-of-service', [LegalController::class, 'termsOfService'])->name('terms-of-service');
Route::get('privacy-policy', [LegalController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('data-sharing-policy', [LegalController::class, 'dataSharingPolicy'])->name('data-sharing-policy');

/**
 * Public views of location map
 */
Route::get('/map', App\Livewire\Public\LocationMap::class)
    ->middleware('throttle:60,1')
    ->name('public.map.locations');

/**
 * Public views of certifications
 */
Route::controller(CertificationController::class)->group(function () {
    Route::get('/certifications/', 'index')->name('public.certification.index');
    Route::get('/certifications/search', 'search')->middleware(ProtectAgainstSpam::class)->name('public.certification.search');
});

/**
 * Onboarding
 */
Route::controller(OnboardingController::class)->group(function () {
    Route::get('/activate-account', 'create')->name('public.activate-account.create');
});

/**
 * Public views of Individuals
 */
Route::controller(IndividualController::class)->group(function () {
    Route::get('/individual', 'create')->name('public.individual.create');
    Route::post('/individual', 'store')
        ->middleware([ProtectAgainstSpam::class, 'throttle:5,1'])
        ->name('public.individual.store');
});

/**
 * Public views of Entities
 */
Route::controller(EntityController::class)->group(function () {
    Route::get('/entity/register', 'create')->name('entity.registration.form');
    Route::post('/entity/register', 'store')
        ->middleware([ProtectAgainstSpam::class, 'throttle:5,1'])
        ->name('entity.registration.submit');
});

/**
 * Public view for Anti-Doping
 */
/*
Route::controller(AntiDopingController::class)->group(function () {
    Route::get('/ad/events', 'index')->name('public.anti-doping.index')
        ->middleware('ensureAntiDopingPinIsValid');
    Route::get('/ad/pin', 'enterPin')->name('public.anti-doping.pin');
    Route::post('/ad/verify-pin', 'verifyPin')->name('public.anti-doping.pin.verify');
    Route::post('/ad/events/download-athletes', 'downloadAthleteList')
        ->name('public.anti-doping.download-athletes')
        ->middleware('ensureAntiDopingPinIsValid');
    Route::post('/ad/events/download-list', 'downloadCompetitionDopingList')
        ->name('public.anti-doping.download-list')
        ->middleware('ensureAntiDopingPinIsValid');
});
*/
Route::get('language/{locale}', [LanguageController::class, 'switchLang'])
    ->name('language.switch');

/**
 * Public view for Entities Profile Page
 */
Route::get('/entities/{entity}', [\App\Http\Controllers\Public\EntityController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('public.entity.show');

Route::middleware(['web', 'throttle:60,1'])->group(function () {
    Route::get('/events', \App\Livewire\Public\EventsCalendar::class)->name('public.events');
    Route::get('/events/{event}', \App\Livewire\Public\EventShow::class)->name('public.event.show');
    Route::get('/diving-professionals', \App\Livewire\Public\DivingProfessionals::class)->name('public.diving-professionals');
    Route::get('/coach-registry', \App\Livewire\Public\CoachRegistry::class)->name('public.coach-registry');
    Route::get('/coach-registry/{individual}', \App\Livewire\Public\CoachProfile::class)->name('public.coach-profile');
    Route::get('/club-registry', \App\Livewire\Public\ClubRegistry::class)->name('public.club-registry');
    Route::get('/technical-official-registry', \App\Livewire\Public\TechnicalOfficialRegistry::class)->name('public.technical-official-registry');
    Route::get('/technical-official-registry/{individual}', \App\Livewire\Public\TechnicalOfficialProfile::class)->name('public.technical-official-profile');
    Route::get('/diving-professionals/{individual}', \App\Livewire\Public\DivingProfessionalProfile::class)->name('public.diving-professional-profile');
    Route::get('/diving-service-providers', \App\Livewire\Public\DivingServiceProviderRegistry::class)->name('public.diving-service-providers');
});

// Secure media routes - outside auth middleware, authorization handled by controller
Route::prefix('secure-media')->name('secure-media.')->group(function () {
    Route::get('/profile/{individual}/{media}', [\App\Http\Controllers\SecureMediaController::class, 'serveProfileImage'])
        ->name('profile');
    Route::get('/profile/{individual}/{media}/thumb', [\App\Http\Controllers\SecureMediaController::class, 'serveProfileThumbnail'])
        ->name('profile.thumb');
});

// Profile Photo Completion - Must be outside the ensure.profile.photo middleware
Route::get('/profile/complete-photo', \App\Livewire\Individual\CompleteProfilePhoto::class)
    ->middleware(['auth', 'check.active.user'])
    ->name('profile.complete-photo');

Route::middleware(['auth', 'check.active.user', 'verified', 'user.relations', 'ensure.profile.photo'])->group(function () {

    // Download exports for signed URLs
    Route::get('/download-export', [\App\Http\Controllers\Common\DownloadExportExcelController::class, 'store'])
        ->name('download.excel.export')
        ->middleware('signed');

    Route::get('/changelog', [\App\Http\Controllers\Common\VersionController::class, 'index'])->name('changelog');

    /* ------------------------------------------------------------------------ */
    /*  Backward-compatibility redirects: legacy international URLs → International namespace */
    /* ------------------------------------------------------------------------ */

    // Redirect legacy individual international URLs to the International namespace
    Route::permanentRedirect(
        'individual/international-license-purchase',
        'international/individual/license-purchase'
    );
    Route::permanentRedirect(
        'individual/international-licenses-attributed',
        'international/individual/licenses-attributed'
    );

    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        if ($user && $user->group) {
            $user_code = strtolower($user->group->code);

            return redirect()->route("{$user_code}.dashboard");
        } else {
            return redirect('/');
        }
    });

    // Logged in
    Route::controller(NotificationsController::class)->group(function () {
        Route::get('/notifications/{id}/read', 'markAsRead')->name('notifications.read');
    });
});
