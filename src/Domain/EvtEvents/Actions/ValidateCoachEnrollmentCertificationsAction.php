<?php

namespace Domain\EvtEvents\Actions;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;

class ValidateCoachEnrollmentCertificationsAction
{
    public function execute(Individual $individual, Event $event): bool
    {
        // Get required certifications from the competition
        $requiredCertifications = $event->competition?->requiredCoachCertifications()
            ->pluck('id')
            ->toArray();

        // If no certifications are required, coach is eligible
        if (empty($requiredCertifications)) {
            return true;
        }

        // Check if the individual has all required active certifications
        $activeCertifications = CertificationAttributed::query()
            ->where('individual_id', $individual->id)
            ->where('status_class', ActiveCertificationAttributedState::class)
            ->whereHas('certification', function ($query) use ($requiredCertifications) {
                $query->whereIn('id', $requiredCertifications);
            })
            ->whereDate('current_term_ends_at', '>', now())
            ->get();

        // Coach must have all required certifications
        return $activeCertifications->count() === count($requiredCertifications);
    }
}
