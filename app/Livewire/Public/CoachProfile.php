<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\ExpiredCertificationAttributedState;
use Domain\Certifications\States\SuspendedCertificationAttributedState;
use Domain\EvtEvents\States\AssignedCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CoachProfile extends Component
{
    public Individual $individual;

    public function mount(Individual $individual): void
    {
        if (! $individual->visible_in_coach_registry) {
            abort(404);
        }

        $individual->load([
            'district',
            'certificationsSportAttributed' => fn ($q) => $q
                ->whereHas('certification', fn (Builder $cq) => $cq
                    ->whereHas('professionalRole', fn (Builder $pr) => $pr->where('role', 'COACH'))
                )
                ->whereIn('status_class', [
                    ActiveCertificationAttributedState::class,
                    ExpiredCertificationAttributedState::class,
                    SuspendedCertificationAttributedState::class,
                ])
                ->with('certification.license.sport'),
            'licenses' => fn ($q) => $q->whereHas('license', function (Builder $lq) {
                $lq->whereHas('committee', fn (Builder $c) => $c->where('code', 'SPORT'))
                    ->whereHas('professionalRole', fn (Builder $pr) => $pr->where('role', 'COACH'));
            })
                ->whereIn('status_class', [
                    ActiveLicenseAttributedState::class,
                    ExpiredLicenseAttributedState::class,
                    SuspendedLicenseAttributedState::class,
                ])
                ->with('license.sport'),
            'coachEnrollments' => fn ($q) => $q
                ->whereIn('status_class', [
                    RegisteredCoachEnrollmentState::class,
                    AssignedCoachEnrollmentState::class,
                ])
                ->whereHas('event')
                ->with(['event.sport', 'entity']),
        ]);

        $this->individual = $individual;
    }

    #[Computed]
    public function certifications(): Collection
    {
        return $this->individual->certificationsSportAttributed
            ->sortByDesc('activated_at')
            ->values();
    }

    public function getCertificationStatus(CertificationAttributed $certification): string
    {
        return match ($certification->status_class) {
            ActiveCertificationAttributedState::class => 'active',
            ExpiredCertificationAttributedState::class => 'expired',
            SuspendedCertificationAttributedState::class => 'suspended',
            default => 'expired',
        };
    }

    public function getCompetitionsProperty(): Collection
    {
        return $this->individual->coachEnrollments
            ->sortByDesc(fn ($enrollment) => $enrollment->event?->start_date);
    }

    public function getLicenseStatusForAttributed(LicenseAttributed $license): string
    {
        return match ($license->status_class) {
            ActiveLicenseAttributedState::class => 'active',
            ExpiredLicenseAttributedState::class => 'expired',
            SuspendedLicenseAttributedState::class => 'suspended',
            default => 'expired',
        };
    }

    public function render(): View
    {
        return view('livewire.public.coach-registry.show')
            ->layout('layouts.public', [
                'title' => $this->individual->name . ' ' . $this->individual->surname,
                'currentPage' => 'coach-registry',
            ]);
    }
}
