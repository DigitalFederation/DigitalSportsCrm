<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\ExpiredCertificationAttributedState;
use Domain\Certifications\States\SuspendedCertificationAttributedState;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
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

class TechnicalOfficialProfile extends Component
{
    public Individual $individual;

    public function mount(Individual $individual): void
    {
        if (! $individual->visible_in_technical_official_registry) {
            abort(404);
        }

        $individual->load([
            'district',
            'certificationsSportAttributed' => fn ($q) => $q
                ->whereHas('certification', fn (Builder $cq) => $cq
                    ->whereHas('professionalRole', fn (Builder $pr) => $pr->where('role', 'TECHNICAL_OFFICIAL'))
                )
                ->whereIn('status_class', [
                    ActiveCertificationAttributedState::class,
                    ExpiredCertificationAttributedState::class,
                    SuspendedCertificationAttributedState::class,
                ])
                ->with('certification.license.sport'),
            'licenses' => fn ($q) => $q->whereHas('license', function (Builder $lq) {
                $lq->whereHas('committee', fn (Builder $c) => $c->where('code', 'SPORT'))
                    ->whereHas('professionalRole', fn (Builder $pr) => $pr->where('role', 'TECHNICAL_OFFICIAL'));
            })
                ->whereIn('status_class', [
                    ActiveLicenseAttributedState::class,
                    ExpiredLicenseAttributedState::class,
                    SuspendedLicenseAttributedState::class,
                ]),
            'refereeEnrollments' => fn ($q) => $q
                ->where('status_class', ActiveRefereeEnrollmentState::class)
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
            ->unique(fn (CertificationAttributed $ca) => $ca->certification?->license?->sport_id)
            ->values();
    }

    #[Computed]
    public function sportSummaries(): Collection
    {
        $refereeEnrollments = RefereeEnrollment::query()
            ->where('individual_id', $this->individual->id)
            ->where('status_class', ActiveRefereeEnrollmentState::class)
            ->whereHas('event')
            ->with(['event.competition.sport'])
            ->get();

        $chiefJudgeRoles = EventRole::query()
            ->where('individual_id', $this->individual->id)
            ->where('role', EventRole::ROLE_CHIEF_JUDGE)
            ->whereHas('event')
            ->with(['event.sport'])
            ->get();

        $sportData = collect();

        foreach ($refereeEnrollments as $enrollment) {
            $sportId = $enrollment->event?->competition?->sport_id;
            $sport = $enrollment->event?->competition?->sport;

            if (! $sportId || ! $sport) {
                continue;
            }

            if (! $sportData->has($sportId)) {
                $sportData[$sportId] = (object) [
                    'referee_evaluations' => collect(),
                    'referee_count' => 0,
                    'chief_judge_count' => 0,
                ];
            }

            $sportData[$sportId]->referee_count++;
            if ($enrollment->evaluation !== null) {
                $sportData[$sportId]->referee_evaluations->push($enrollment->evaluation);
            }
        }

        foreach ($chiefJudgeRoles as $role) {
            $sportId = $role->event?->sport?->id;

            if (! $sportId) {
                continue;
            }

            if (! $sportData->has($sportId)) {
                $sportData[$sportId] = (object) [
                    'referee_evaluations' => collect(),
                    'referee_count' => 0,
                    'chief_judge_count' => 0,
                ];
            }

            $sportData[$sportId]->chief_judge_count++;
        }

        return $sportData->map(function ($data) {
            $refereeExp = $data->referee_evaluations->isNotEmpty() ? (int) $data->referee_evaluations->sum() : 0;
            $chiefJudgeExp = $data->chief_judge_count * 10;
            $totalExp = $refereeExp + $chiefJudgeExp;

            $allEvaluations = $data->referee_evaluations->toArray();
            for ($i = 0; $i < $data->chief_judge_count; $i++) {
                $allEvaluations[] = 5.0;
            }

            $avgEvaluation = ! empty($allEvaluations) ? round(collect($allEvaluations)->avg(), 1) : null;

            return (object) [
                'total_experience_points' => $totalExp > 0 ? $totalExp : null,
                'average_evaluation' => $avgEvaluation,
            ];
        });
    }

    #[Computed]
    public function competitions(): Collection
    {
        return $this->individual->refereeEnrollments
            ->sortByDesc(fn ($enrollment) => $enrollment->event?->start_date);
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

    public function getLicenseStatus(LicenseAttributed $license): string
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
        return view('livewire.public.technical-official-registry.show')
            ->layout('layouts.public', [
                'title' => $this->individual->name . ' ' . $this->individual->surname,
                'currentPage' => 'technical-official-registry',
            ]);
    }
}
