<?php

namespace App\View\Components\Certification;

use Carbon\Carbon;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\DirectorApprovalCertificationAttributedState;
use Domain\Certifications\States\DirectorApprovedCertificationAttributedState;
use Domain\Certifications\States\SuspendedCertificationAttributedState;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Layout extends Component
{
    public $certification;
    public $userType;
    public $allowedActions;
    public $canDownloadCard;
    public $canGeneratePdf;
    public $routePrefix;
    public $cardRouteName;

    public function __construct(CertificationAttributed $certification, string $cardRouteName = 'certification-card')
    {
        $this->certification = $certification;
        $this->cardRouteName = $cardRouteName;
        $this->userType = auth()->user()->group->code;
        $this->routePrefix = strtolower($this->userType);

        $this->determineAllowedActions();
        $this->canDownloadCard = $this->canDownloadCard();
        $this->canGeneratePdf = $this->canGeneratePdf();
    }

    protected function determineAllowedActions()
    {
        $user = Auth::user();
        $certification = $this->certification;

        $isFederation = $user->isFederation();
        $isLocalFederation = $isFederation && $user->federations()->first()->is_local;
        $isAdmin = $user->group->code == 'ADMIN';
        $isInstructor = $user->individuals()->exists() && $user->individuals()->first()->id === $certification->instructor_id;

        $isActiveCertification = $certification->isActive() && $certification->status_class == ActiveCertificationAttributedState::class;
        $isSuspendedCertification = ! $certification->isActive() && $certification->status_class == SuspendedCertificationAttributedState::class;
        $isPendingCertification = ! $certification->isActive() && (
            $certification->stateName() == 'pending' ||
            $certification->stateName() == 'provisional' ||
            $certification->status_class == DirectorApprovedCertificationAttributedState::class
        );

        $isExpired = false;
        if (! empty($certification->current_term_ends_at)) {
            $expirationDate = Carbon::parse($certification->current_term_ends_at);
            $isExpired = $expirationDate->isPast();
        }

        $canUnsuspend = $isSuspendedCertification && ! $isExpired;

        $this->allowedActions = [
            'approveRequest' => $isInstructor && $certification->status_class === DirectorApprovalCertificationAttributedState::class,
            'validateCertification' => $isPendingCertification && ($isFederation || $isAdmin) && ! empty($certification->national_code) && (! $isLocalFederation || $isAdmin),
            'rejectCertification' => $isPendingCertification && ($isFederation || $isAdmin),
            'editCertification' => $isPendingCertification && ($isFederation || $isAdmin) && (! $isLocalFederation || $isAdmin),
            'suspendCertification' => $isActiveCertification && ($isFederation || $isAdmin),
            'activateCertification' => $canUnsuspend && ($isFederation || $isAdmin),
        ];
    }

    protected function canDownloadCard(): bool
    {
        return in_array($this->userType, ['ADMIN', 'FEDERATION', 'INDIVIDUAL'])
            && $this->certification->isActive();
    }

    protected function canGeneratePdf(): bool
    {
        return in_array($this->userType, ['ADMIN', 'FEDERATION', 'INDIVIDUAL'])
            && $this->certification->isActive();
    }

    public function render()
    {
        return view('components.certification.layout', [
            'route_prefix' => $this->routePrefix,
            'allowedActions' => $this->allowedActions,
            'cardRouteName' => $this->cardRouteName,
        ]);
    }
}
