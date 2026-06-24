<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\SubRegion;
use Domain\Federations\Models\Federation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FederationController extends Controller
{
    public function index(): View
    {
        $entity = auth()->user()->entities()->first();

        if (! $entity) {
            abort(403, 'No entity associated with this user');
        }

        $federations = QueryBuilder::for(Federation::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::scope('filter_code'),
                AllowedFilter::scope('filter_committee'),
                AllowedFilter::scope('filter_zone'),
                AllowedFilter::scope('filter_region'),
                AllowedFilter::scope('filter_is_local'),
            ])
            ->whereHas('entityFederations', function (Builder $query) use ($entity) {
                $query->where('entity_id', $entity->id);
            })
            ->with([
                'country.geoZone',
                'country.subRegion',
                'entityFederations' => function ($query) use ($entity) {
                    $query->where('entity_id', $entity->id);
                },
                'entities' => function ($query) use ($entity) {
                    return $query->where('entity_id', $entity->id);
                }])
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $cmasZones = GeoZone::select('id', 'name')->orderBy('name')->get();
        $subRegions = SubRegion::select('id', 'name')->orderBy('name')->get();

        return view('web.entity.federation.index', compact('federations', 'countries', 'committees', 'cmasZones', 'subRegions'));
    }

    public function show(int $id): View
    {
        $entity = auth()->user()->entities()->first();

        if (! $entity) {
            abort(403, 'No entity associated with this user');
        }

        $federation = Federation::whereHas('entityFederations', function (Builder $query) use ($entity) {
            $query->where('entity_id', $entity->id);
        })->findOrFail($id);

        return view('web.entity.federation.show', compact('federation'));
    }
}
