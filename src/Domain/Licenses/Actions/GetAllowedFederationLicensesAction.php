<?php

namespace Domain\Licenses\Actions;

use App\Models\Committee;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @mixin \Domain\Licenses\Actions\GetAllowedFederationLicensesAction
 */
class GetAllowedFederationLicensesAction
{
    public function __invoke(int $federation_id, ?string $committee = null, ?string $license_type_name = null, ?string $professional_role = null): array|Collection
    {
        $federation = Federation::find($federation_id);

        if ($federation?->is_local) {
            return $this->getLocalFederationLicenses($federation_id, $committee, $license_type_name, $professional_role);
        }

        return $this->getMainFederationLicenses($federation_id, $committee, $license_type_name, $professional_role);
    }

    /**
     * Return licenses directly assigned to a federation
     */
    public function getMainFederationLicenses(
        int $federation_id,
        ?string $committee = null,
        ?string $license_type_name = null,
        ?string $professional_role = null): array|Collection
    {
        // Detect Committee and License Type ID
        $committeeId = Committee::where('code', strtoupper($committee))->first()->id;
        $licenseTypeId = LicenseType::where('name', $license_type_name)->first()->id;
        $professionalRoleIds = $this->resolveProfessionalRoleId($professional_role);

        $licensesQuery = License::query()
            ->whereHas('federations', function (Builder $query) use ($federation_id) {
                $query->where('federation_id', $federation_id);
            })
            ->where('committee_id', $committeeId)
            ->where('type_id', $licenseTypeId)
            ->where('active', 1)
            ->when($professionalRoleIds, function (Builder $query, Collection $professionalRoleIds) {
                $query->whereIn('professional_role_id', $professionalRoleIds);
            });

        $licenses = $licensesQuery
            ->get(['id', 'name'])
            ->toArray();

        return $licenses;
    }

    protected function getLocalFederationLicenses(int $localFederationId, string $committee, string $license_type_name, ?string $professional_role = null): Collection
    {
        // Detect Committee and License Type ID
        $committeeId = Committee::where('code', strtoupper($committee))->first()->id;
        $licenseTypeId = LicenseType::where('name', $license_type_name)->first()->id;
        $professionalRoleIds = $this->resolveProfessionalRoleId($professional_role);

        // Fetch licenses directly assigned to the local federation
        $licensesQuery = License::query()
            ->whereHas('federations', function (Builder $query) use ($localFederationId) {
                $query->where('federation_id', $localFederationId);
            })
            ->where('committee_id', $committeeId)
            ->where('type_id', $licenseTypeId)
            ->where('active', 1)
            ->when($professionalRoleIds, function (Builder $query, Collection $professionalRoleIds) {
                $query->whereIn('professional_role_id', $professionalRoleIds);
            });

        $licenses = $licensesQuery
            ->get(['id', 'name'])
            ->toArray();

        return collect($licenses);
    }

    private function resolveProfessionalRoleId(?string $professional_role): ?Collection
    {
        if (empty($professional_role)) {
            return null;
        }

        $role = match ($professional_role) {
            'INSTRUCTORLEADER' => 'INSTRUCTOR',
            'REFEREEJUDGE' => 'TECHNICAL_OFFICIAL',
            default => $professional_role,
        };

        return ProfessionalRole::select('id')->where('role', $role)->get();
    }

}
