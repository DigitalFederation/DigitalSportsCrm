<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Policies\ApplicationDocumentPolicy;
use App\Policies\DivingProfessionalCertificationPolicy;
use App\Policies\DivingTechnicalDirectorInvitationPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\InsurancePolicy;
use App\Policies\LicenseAttributedPolicy;
use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\Models\DivingTechnicalDirectorInvitation;
use Domain\Documents\Models\Document;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\Insurance\Models\Insurance;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        ApplicationDocument::class => ApplicationDocumentPolicy::class,
        Document::class => DocumentPolicy::class,
        Insurance::class => InsurancePolicy::class,
        DivingProfessionalCertification::class => DivingProfessionalCertificationPolicy::class,
        DivingTechnicalDirectorInvitation::class => DivingTechnicalDirectorInvitationPolicy::class,
        LicenseAttributed::class => LicenseAttributedPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
