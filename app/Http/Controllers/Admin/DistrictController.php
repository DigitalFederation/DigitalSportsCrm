<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DistrictRequest;
use Domain\Geographic\Models\District;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DistrictController extends Controller
{
    public function index(): View
    {
        $districts = QueryBuilder::for(District::class)
            ->withCount(['zones', 'entities', 'federations', 'individuals'])
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    return $query->search($value);
                }),
                'is_active',
            ])
            ->defaultSort('name')
            ->paginate()
            ->appends(request()->query());

        return view('web.admin.districts.index', compact('districts'));
    }

    public function create(): View
    {
        return view('web.admin.districts.create');
    }

    public function store(DistrictRequest $request): RedirectResponse
    {
        $district = District::create($request->validated());

        return redirect()
            ->route('admin.districts.index')
            ->with('success', 'District created successfully.');
    }

    public function show(District $district): View
    {
        $district->load(['zones', 'entities', 'federations', 'individuals']);

        return view('web.admin.districts.show', compact('district'));
    }

    public function edit(District $district): View
    {
        return view('web.admin.districts.edit', compact('district'));
    }

    public function update(DistrictRequest $request, District $district): RedirectResponse
    {
        $district->update($request->validated());

        return redirect()
            ->route('admin.districts.index')
            ->with('success', 'District updated successfully.');
    }

    public function destroy(District $district): RedirectResponse
    {
        // Check if district has any associations
        if ($district->entities()->exists() ||
            $district->federations()->exists() ||
            $district->individuals()->exists() ||
            $district->zones()->exists()) {

            return redirect()
                ->route('admin.districts.index')
                ->with('error', 'Cannot delete district that has associated entities, federations, individuals, or zones.');
        }

        $district->delete();

        return redirect()
            ->route('admin.districts.index')
            ->with('success', 'District deleted successfully.');
    }

    public function toggleStatus(District $district): RedirectResponse
    {
        $district->update(['is_active' => ! $district->is_active]);

        $status = $district->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('admin.districts.index')
            ->with('success', "District {$status} successfully.");
    }
}
