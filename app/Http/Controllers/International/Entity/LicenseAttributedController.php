<?php

namespace App\Http\Controllers\International\Entity;

use App\Http\Controllers\Common\BaseLicenseAttributedController;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * international Entity License Attributed Controller
 *
 * Handles INTERNATIONAL licenses ONLY (is_international = true) for entities
 * National licenses are handled in regular Entity namespace
 */
class LicenseAttributedController extends BaseLicenseAttributedController
{
    /**
     * Display a listing of international licenses for the entity.
     */
    public function index(): View
    {
        $user = auth()->user();
        $entity = $user->entities()->first();

        if (! $entity) {
            abort(403, __('admin.error.no_entity_profile'));
        }

        // Permission check handled by route middleware

        // Get committee from filter
        $committee = request()->filter['committee'] ?? null;

        $query = QueryBuilder::for(LicenseAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee'),
                AllowedFilter::scope('filter_holder_type', 'holder_type'),
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_federation', 'federation'),
                AllowedFilter::scope('filter_entity', 'entity'),
                AllowedFilter::scope('filter_country', 'country'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_category', 'professionalRole'),
                AllowedFilter::scope('filter_name', 'license_name'),
                AllowedFilter::scope('filter_status', 'license_attributed_status'),
            ])
            ->where(['model_type' => 'entity', 'model_id' => $entity->id])
            // CRITICAL: Remove global scope to access international licenses
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            // CRITICAL: Only show INTERNATIONAL licenses
            ->whereHas('license', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            });

        $licenses = $query->with('federation.country', 'owner', 'license.committee', 'license.sport')
            ->paginate()
            ->appends(request()->query());

        $federations = Federation::select('id', 'name')
            ->whereHas('licenses', function ($query) {
                $query->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            })
            ->orderBy('name')
            ->get();

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();
        $cmas_zones = GeoZone::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Suspended')],
        ];

        return view('web.international.entity.licenses-attributed.index', compact(
            'licenses',
            'committee',
            'filter_status',
            'sports',
            'professional_roles',
            'federations',
            'countries',
            'cmas_zones'
        ));
    }

    /**
     * Display the specified international license.
     */
    public function show(string $id): View|RedirectResponse
    {
        $user = auth()->user();
        $entity = $user->entities()->first();

        if (! $entity) {
            abort(403, __('admin.error.no_entity_profile'));
        }

        $query = LicenseAttributed::with('owner', 'license.committee', 'license.sport', 'federation')
            ->where('id', $id)
            ->where('model_type', 'entity')
            ->where('model_id', $entity->id)
            // CRITICAL: Remove scope and filter for international only
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('license', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            });

        $license = $query->firstOrFail();

        return view('web.international.entity.licenses-attributed.show', compact('license'));
    }

    /**
     * Display international licenses for entity's individuals.
     */
    public function individuals(): View
    {
        $user = auth()->user();
        $entity = $user->entities()->first();

        if (! $entity) {
            abort(403, __('admin.error.no_entity_profile'));
        }

        // Check if user has permission to view member licenses
        if (! $user->hasRole(['entity-admin', 'entity-operator'])) {
            abort(403, __('admin.error.no_permission_member_licenses'));
        }

        // Get individuals associated with the entity - verify they are active members
        $individualIds = $entity->individuals()
            ->wherePivot('status_class', ActiveIndividualEntityState::class)
            ->pluck('individual_id');

        // Query international licenses for entity's individuals
        $query = QueryBuilder::for(LicenseAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee'),
                AllowedFilter::scope('filter_holder_type', 'holder_type'),
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_federation', 'federation'),
                AllowedFilter::scope('filter_country', 'country'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_category', 'professionalRole'),
                AllowedFilter::scope('filter_name', 'license_name'),
                AllowedFilter::scope('filter_status', 'license_attributed_status'),
            ])
            ->where('model_type', 'individual')
            ->whereIn('model_id', $individualIds)
            // CRITICAL: Remove scope and filter for international only
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('license', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            });

        $licenses = $query->with('federation.country', 'owner', 'license.committee', 'license.sport')
            ->paginate()
            ->appends(request()->query());

        return view('web.international.entity.member-licenses.index', compact('licenses', 'entity'));
    }
}
