<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Common\BaseLicenseAttributedController;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\Sport;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LicenseAttributedController extends BaseLicenseAttributedController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Check if this is a sport committee request (from filter parameter)
        $committee = request()->filter['committee'] ?? null;
        $isSportCommittee = $committee === 'sport';

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
            ->where(['model_type' => 'individual', 'model_id' => auth()->user()->individuals()->first()->id]);

        // Default behavior: show only national licenses (exclude international)
        // International licenses are now handled in InternationalLicenseAttributedController
        $query->whereHas('license', function ($q) {
            $q->whereHas('committee', fn ($cq) => $cq->where('is_international', false));
        });

        $licenses = $query->with('federation.country', 'owner')
            ->paginate()
            ->appends(request()->query());

        $federations = Federation::select('id', 'name')->whereHas('individuals', function (Builder $query) {
            $query->where('individual_id', auth()->user()->individuals()->first()->id);
        })->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();
        $cmas_zones = GeoZone::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Suspended')],
        ];

        $title = '';
        if (! empty(request()->input('filter')['committee'])) {
            $title = ucwords(request()->input('filter')['committee']);

            if (! empty(request()->input('filter')['filter_holder_type'])) {
                $title .= ' ' . ucwords(request()->input('filter')['filter_holder_type']);
            } else {
                $title .= ' ' . __(' Licenses');
            }
        } else {
            if (! empty(request()->input('filter')['filter_holder_type'])) {
                $title = ucwords(request()->input('filter')['filter_holder_type']) . ' ' . __('Licenses');
            } else {
                $title = __('All Licenses');
            }
        }

        return view('web.individual.license_attributed.index', compact(
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

    /**
     * Show the form for creating a new resource.
     *
     * @param  string  $type  [Committee Code]
     */
    public function create(string $type): View
    {

        if ($type !== 'sport') {
            abort(404);
        }

        $individual = auth()->user()->individuals()->first();
        $requester_model_type = 'individual';

        // Get the licenses for sport committee
        $licenses = License::where(function ($query) {
            $query->whereHas('committee', function (Builder $query) {
                $query->where('code', 'sport');
            })->whereHas('type', function (Builder $query) {
                $query->where('is_individual', 1);
            })->where('requires_cmas_approval', 1);
        })
            ->where('requester_model', Individual::class)
            ->get();

        return view('web.individual.license_attributed.create', compact(
            'type',
            'licenses',
            'requester_model_type',
            'individual'
        ));
    }
}
