<?php

namespace App\Providers;

use App\Events\ActivateAfterPayment;
use App\Events\AttachmentFileCreatedEvent;
use App\Events\CertificationAttributedCreatedEvent;
use App\Events\DocumentMarkedAsPaid;
use App\Events\LicenseAttributedCreatedEvent;
use App\Listeners\ActivateAfterPaymentCertificationAttributedListener;
use App\Listeners\ActivateAfterPaymentEnrollmentListener;
use App\Listeners\ActivateAfterPaymentIndividualEnrollmentListener;
use App\Listeners\ActivateAfterPaymentLicenseAttributedListener;
use App\Listeners\ActivateAfterPaymentMembershipListener;
use App\Listeners\ActivateAfterPaymentMemberSubscriptionListener;
use App\Listeners\AdminNotificationForLicenseAttributedListener;
use App\Listeners\CheckIfIndividualIsInstructor;
use App\Listeners\CheckIfUserHasOneOfTypeOfGroup;
use App\Listeners\ClearAttachmentCache;
use App\Listeners\CreateCertificationAttributedDocumentListener;
use App\Listeners\CreateLicenseAttributedDocumentListener;
use App\Listeners\DispatchInvoiceGenerationListener;
use App\Listeners\LastLoggedIn;
use App\Observers\CertificationAttributedInstructorObserver;
use Domain\Certifications\Models\CertificationAttributedInstructor;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            CheckIfIndividualIsInstructor::class,
            CheckIfUserHasOneOfTypeOfGroup::class,
            LastLoggedIn::class,
        ],
        ActivateAfterPayment::class => [
            ActivateAfterPaymentMembershipListener::class,
            ActivateAfterPaymentCertificationAttributedListener::class,
            ActivateAfterPaymentLicenseAttributedListener::class,
            ActivateAfterPaymentIndividualEnrollmentListener::class,
            ActivateAfterPaymentEnrollmentListener::class,
            ActivateAfterPaymentMemberSubscriptionListener::class,
        ],
        LicenseAttributedCreatedEvent::class => [
            CreateLicenseAttributedDocumentListener::class,
            AdminNotificationForLicenseAttributedListener::class,
        ],
        CertificationAttributedCreatedEvent::class => [
            CreateCertificationAttributedDocumentListener::class,
        ],
        AttachmentFileCreatedEvent::class => [
            ClearAttachmentCache::class,
        ],
        DocumentMarkedAsPaid::class => [
            DispatchInvoiceGenerationListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        CertificationAttributedInstructor::observe(CertificationAttributedInstructorObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
