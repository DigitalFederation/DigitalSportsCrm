<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\SubRegion;
use Domain\Federations\Models\Federation;
use Domain\Users\Actions\SyncUserRolesAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FederationController extends Controller
{
    public function index(): View
    {
        $individual_id = auth()->user()->individuals()->first()->id;

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
            ->whereHas('individualFederations', function (Builder $query) use ($individual_id) {
                $query->where('individual_id', $individual_id);
            })
            ->with([
                'country.geoZone',
                'country.subRegion',
                'individualFederations' => function ($query) use ($individual_id) {
                    $query->where('individual_id', $individual_id);
                }])
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $cmasZones = GeoZone::select('id', 'name')->orderBy('name')->get();
        $subRegions = SubRegion::select('id', 'name')->orderBy('name')->get();

        return view('web.individual.federation.index', compact('federations', 'countries', 'committees', 'cmasZones', 'subRegions'));
    }

    public function show(int $id): View
    {
        $federation = Federation::findOrFail($id);

        return view('web.individual.federation.show', compact('federation'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $federationId): RedirectResponse
    {
        $federation = Federation::findOrFail($federationId);

        // Prevent removal from main federation
        if ($federation->is_default_federation) {
            return redirect()
                ->route('individual.federation.index')
                ->with('error', __('individuals.cannot_disassociate_main_federation'));
        }

        $individual = auth()->user()->individuals()->first();

        try {
            // Detach the individual from the specified federation
            $individual->federations()->detach($federationId);

            // Optionally, sync user roles after detaching the federation
            $syncUserRolesAction = new SyncUserRolesAction;
            $syncUserRolesAction->execute($individual->user);

            return redirect()->route('individual.federation.index')->with('success', 'Federation detached successfully.');
        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return redirect()->route('individual.federation.index')->with('error', 'Error detaching federation: ' . $ex->getMessage());
        }
    }
}
