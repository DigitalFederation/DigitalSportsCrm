<?php

declare(strict_types=1);

namespace Domain\Individuals\Actions;

use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds a query for eligible instructors based on committee requirements.
 *
 * For DIVING and SCIENTIFIC committees: requires BOTH active instructor certification AND active instructor license.
 * For other committees (SPORT, DIVINGSERVICES, etc.): requires active instructor license OR professional role in entity.
 */
class GetEligibleInstructorsQueryAction
{
    /**
     * Committees that require both certification AND license for instructors.
     */
    private const COMMITTEES_REQUIRING_BOTH = ['diving', 'scientific'];

    /**
     * Build a query for eligible instructors.
     *
     * @param  int|null  $schoolId  The school/entity ID to filter by
     * @param  int|null  $federationId  The federation ID to filter by
     * @param  string|null  $committeeCode  The committee code (e.g., 'diving', 'scientific', 'sport')
     * @param  string|null  $excludeIndividualId  Individual ID to exclude from results
     * @return Builder The query builder for eligible instructors
     */
    public function __invoke(
        ?int $schoolId = null,
        ?int $federationId = null,
        ?string $committeeCode = null,
        ?string $excludeIndividualId = null
    ): Builder {
        $query = Individual::query()
            ->when(
                $schoolId,
                fn ($q) => $this->applyEntityInstructorFilter($q, $schoolId, $committeeCode)
            )
            ->when(
                $federationId && ! $schoolId,
                fn ($q) => $q->whereHas('federations', fn ($sq) => $sq->where('federation.id', $federationId))
            );

        if ($excludeIndividualId) {
            $query->where('individual.id', '!=', $excludeIndividualId);
        }

        // For DIVING and SCIENTIFIC: require BOTH certification AND license
        if ($this->requiresBothCertificationAndLicense($committeeCode)) {
            $this->applyCertificationAndLicenseFilters($query, $committeeCode);
        } else {
            $this->applyStandardInstructorFilters($query, $schoolId, $federationId, $committeeCode);
        }

        return $query;
    }

    /**
     * Filter by entity association. For committees requiring both certification and license,
     * also require an active instructor professional role at the entity.
     */
    protected function applyEntityInstructorFilter(Builder $query, int $schoolId, ?string $committeeCode): Builder
    {
        if ($this->requiresBothCertificationAndLicense($committeeCode)) {
            return $query->whereHas('professionalRoleEntities', function (Builder $roleQuery) use ($schoolId) {
                $roleQuery->where('entity_id', $schoolId)
                    ->where('status_class', ActiveEntityProfessionalRoleState::class)
                    ->whereHas('professionalRole', fn (Builder $prq) => $prq->where('role', 'like', '%INSTRUCTOR%'));
            });
        }

        return $query->whereHas('entities', fn ($sq) => $sq->where('entity.id', $schoolId));
    }

    /**
     * Check if committee requires both certification and license.
     */
    protected function requiresBothCertificationAndLicense(?string $committeeCode): bool
    {
        return in_array(strtolower($committeeCode ?? ''), self::COMMITTEES_REQUIRING_BOTH, true);
    }

    /**
     * Apply filters for committees requiring BOTH certification AND license.
     * Used by DIVING and SCIENTIFIC committees.
     */
    protected function applyCertificationAndLicenseFilters(Builder $query, string $committeeCode): void
    {
        $normalizedCode = strtolower($committeeCode);

        // 1. Must have active instructor CERTIFICATION for the committee
        $query->whereHas('certificationsAttributed', function (Builder $certQuery) use ($normalizedCode) {
            $certQuery->where('status_class', ActiveCertificationAttributedState::class)
                ->whereHas('certification', function (Builder $cq) use ($normalizedCode) {
                    $cq->whereHas('committee', fn (Builder $cmq) => $cmq->whereRaw('LOWER(code) = ?', [$normalizedCode]))
                        ->whereHas('professionalRole', fn (Builder $prq) => $prq->where('role', 'like', '%INSTRUCTOR%'));
                });
        });

        // 2. Must have active instructor LICENSE for the committee
        $query->whereHas('licenses', function (Builder $licQuery) use ($normalizedCode) {
            $licQuery->withoutGlobalScope(ExcludeInternationalScope::class)
                ->where('status_class', ActiveLicenseAttributedState::class)
                ->whereHas('license', function (Builder $l) use ($normalizedCode) {
                    $l->withoutGlobalScope(ExcludeInternationalScope::class)
                        ->whereHas('committee', fn (Builder $cq) => $cq->whereRaw('LOWER(code) = ?', [$normalizedCode]))
                        ->whereHas('professionalRole', fn (Builder $pr) => $pr->where('role', 'like', '%INSTRUCTOR%'));
                });
        });
    }

    /**
     * Apply standard filters for other committees (SPORT, DIVINGSERVICES, etc.).
     * Maintains existing behavior: license OR professional role in entity.
     */
    protected function applyStandardInstructorFilters(
        Builder $query,
        ?int $schoolId,
        ?int $federationId,
        ?string $committeeCode
    ): void {
        if ($schoolId) {
            // If a school is selected, instructor must have an active role in THAT school.
            $query->whereHas('professionalRoleEntities', function (Builder $roleQuery) use ($schoolId, $committeeCode) {
                $roleQuery->where('entity_id', $schoolId)
                    ->where('status_class', ActiveEntityProfessionalRoleState::class)
                    ->whereHas('professionalRole', function (Builder $prQuery) use ($committeeCode) {
                        $prQuery->where('role', 'like', '%INSTRUCTOR%');
                        if ($committeeCode) {
                            $prQuery->whereHas('committee', fn (Builder $cq) => $cq->whereRaw('LOWER(code) = ?', [strtolower($committeeCode)]));
                        }
                    });
            });
        } elseif ($federationId) {
            // If a federation is selected (but no school),
            // instructor must have an active instructor license.
            $query->whereHas('licenses', function (Builder $licQuery) use ($committeeCode) {
                $licQuery->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereHas('license', function (Builder $l) use ($committeeCode) {
                        $l->withoutGlobalScope(ExcludeInternationalScope::class)
                            ->whereHas('professionalRole', function (Builder $pr) {
                                $pr->where('role', 'like', '%INSTRUCTOR%');
                            });
                        if ($committeeCode) {
                            $l->whereHas('committee', fn (Builder $cq) => $cq->whereRaw('LOWER(code) = ?', [strtolower($committeeCode)]));
                        }
                    });
            });
        } else {
            // If neither a school nor a federation is selected, return no results
            // to prevent listing all instructors unintentionally.
            $query->whereRaw('1 = 0');
        }
    }
}
