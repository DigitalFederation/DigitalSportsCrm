<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZoneRequest;
use App\Models\Country;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ZoneController extends Controller
{
    public function index(): View
    {
        $zones = QueryBuilder::for(Zone::class)
            ->with(['creator', 'districts.country'])
            ->withCount(['districts', 'entities', 'federations', 'individuals'])
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    return $query->search($value);
                }),
                'is_active',
                AllowedFilter::callback('country', function ($query, $value) {
                    return $query->byCountry($value);
                }),
            ])
            ->defaultSort('name')
            ->paginate()
            ->appends(request()->query());

        $countries = Country::orderBy('name')->get();

        return view('web.admin.zones.index', compact('zones', 'countries'));
    }

    public function create(): View
    {
        $districts = District::with('country')
            ->active()
            ->orderBy('name')
            ->get()
            ->groupBy('country.name');

        return view('web.admin.zones.create', compact('districts'));
    }

    public function store(ZoneRequest $request): RedirectResponse
    {
        $zoneData = $request->validated();
        $zoneData['created_by'] = auth()->id();

        $zone = Zone::create($zoneData);

        // Attach selected districts
        if ($request->has('district_ids')) {
            $zone->districts()->attach($request->district_ids);
        }

        return redirect()
            ->route('admin.zones.index')
            ->with('success', 'Zone created successfully.');
    }

    public function show(Zone $zone): View
    {
        $zone->load([
            'creator',
            'districts.country',
            'entities',
            'federations',
            'individuals',
        ]);

        return view('web.admin.zones.show', compact('zone'));
    }

    public function edit(Zone $zone): View
    {
        $zone->load('districts');

        $districts = District::with('country')
            ->active()
            ->orderBy('name')
            ->get()
            ->groupBy('country.name');

        $selectedDistrictIds = $zone->districts->pluck('id')->toArray();

        return view('web.admin.zones.edit', compact('zone', 'districts', 'selectedDistrictIds'));
    }

    public function update(ZoneRequest $request, Zone $zone): RedirectResponse
    {
        $zone->update($request->validated());

        // Sync selected districts
        $districtIds = $request->district_ids ?? [];
        $zone->districts()->sync($districtIds);

        return redirect()
            ->route('admin.zones.index')
            ->with('success', 'Zone updated successfully.');
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        // Check if zone has any associations
        if ($zone->entities()->exists() ||
            $zone->federations()->exists() ||
            $zone->individuals()->exists()) {

            return redirect()
                ->route('admin.zones.index')
                ->with('error', 'Cannot delete zone that has associated entities, federations, or individuals.');
        }

        // Detach all districts before deletion
        $zone->districts()->detach();
        $zone->delete();

        return redirect()
            ->route('admin.zones.index')
            ->with('success', 'Zone deleted successfully.');
    }

    public function toggleStatus(Zone $zone): RedirectResponse
    {
        $zone->update(['is_active' => ! $zone->is_active]);

        $status = $zone->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('admin.zones.index')
            ->with('success', "Zone {$status} successfully.");
    }

    public function manageDistricts(Zone $zone): View
    {
        $zone->load('districts.country');

        $availableDistricts = District::with('country')
            ->active()
            ->whereNotIn('id', $zone->districts->pluck('id'))
            ->orderBy('name')
            ->get()
            ->groupBy('country.name');

        return view('web.admin.zones.manage-districts', compact('zone', 'availableDistricts'));
    }

    public function updateDistricts(ZoneRequest $request, Zone $zone): RedirectResponse
    {
        $districtIds = $request->district_ids ?? [];
        $zone->districts()->sync($districtIds);

        return redirect()
            ->route('admin.zones.show', $zone)
            ->with('success', 'Zone districts updated successfully.');
    }
}
