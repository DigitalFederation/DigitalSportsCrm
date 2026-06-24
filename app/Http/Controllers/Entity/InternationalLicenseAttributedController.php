<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InternationalLicenseAttributedController extends Controller
{
    /**
     * Display a listing of international licenses.
     */
    public function index(): View
    {
        $entity = Auth::user()->getEntity();

        if (! $entity) {
            abort(403, __('No entity associated with this user'));
        }

        // Check if user has permission for international license access
        if (! Auth::user()->can('access international licenses')) {
            abort(403, __('Your entity does not have access to international licenses'));
        }

        // Query international licenses only
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
            // Remove the global scope to access international licenses
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            // Only show international licenses
            ->whereHas('license', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            });

        $licenses = $query->with('federation.country', 'owner')
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

        $title = __('International Licenses');

        return view('web.entity.international-licenses-attributed.index', compact(
            'licenses',
            'federations',
            'countries',
            'sports',
            'filter_status',
            'title',
            'professional_roles',
            'cmas_zones',
            'entity'
        ));
    }

    /**
     * Display international licenses for entity's individuals
     */
    public function individuals(): View
    {
        $entity = Auth::user()->getEntity();

        if (! $entity) {
            abort(403, __('No entity associated with this user'));
        }

        // Check if user has permission for international license access
        if (! Auth::user()->can('access international licenses')) {
            abort(403, __('Your entity does not have access to international licenses'));
        }

        // Check if user has permission to view member licenses
        if (! Auth::user()->hasRole(['entity-admin', 'entity-admin', 'entity-operator'])) {
            abort(403, __('You do not have permission to view member licenses'));
        }

        // Get individuals associated with the entity - verify they are active members
        $individualIds = $entity->individuals()
            ->where('status', 'active')
            ->pluck('individual_id');

        // Query international licenses for entity's individuals
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
            ->where('model_type', 'individual')
            ->whereIn('model_id', $individualIds)
            // Remove the global scope to access international licenses
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            // Only show international licenses
            ->whereHas('license', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            });

        $licenses = $query->with('federation.country', 'owner')
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

        $title = __('Members International Licenses');

        return view('web.entity.international-licenses-attributed.individuals', compact(
            'licenses',
            'federations',
            'countries',
            'sports',
            'filter_status',
            'title',
            'professional_roles',
            'cmas_zones',
            'entity'
        ));
    }
}
