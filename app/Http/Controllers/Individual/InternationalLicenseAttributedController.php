<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Common\BaseLicenseAttributedController;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\Sport;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InternationalLicenseAttributedController extends BaseLicenseAttributedController
{
    /**
     * Display a listing of international licenses for the individual.
     */
    public function index(): View
    {
        $individual = auth()->user()->individuals()->first();

        if (! $individual) {
            abort(403, __('No individual profile associated with this user'));
        }

        // Check if user has permission for international license access
        if (! auth()->user()->can('access international licenses')) {
            abort(403, __('You do not have access to international licenses'));
        }

        // Check if individual has active affiliation
        if (! $individual->hasActiveAffiliation()) {
            abort(403, __('You must have an active affiliation to view international licenses'));
        }

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
            ->where(['model_type' => 'individual', 'model_id' => $individual->id])
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

        return view('web.individual.international-licenses-attributed.index', compact(
            'licenses',
            'federations',
            'countries',
            'sports',
            'filter_status',
            'title',
            'professional_roles',
            'cmas_zones'
        ));
    }
}
