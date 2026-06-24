<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualProfessionalRole;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StaffProfessionalRolesController extends Controller
{
    public function index(Request $request)
    {

        $cacheKey = 'staff_professional_roles_index_' . md5($request->getQueryString());

        $professional_roles = QueryBuilder::for(IndividualProfessionalRole::class)
            ->allowedFilters([
                AllowedFilter::exact('professionalRole.id'),
                AllowedFilter::partial('professionalRole.name'),
            ])
            ->with('individual:id,member_number,name,surname', 'professionalRole:id,name')
            ->whereHas('professionalRole', function ($query) {
                $query->whereIn('role', ['STAFF', 'FEDERATION_STAFF']);
            })
            ->whereHas('individual')
            ->paginate(15)
            ->appends($request->query());

        $staff_roles = ProfessionalRole::whereIn('role', ['STAFF', 'FEDERATION_STAFF'])->get();

        return view('web.admin.staff_roles.index', compact('professional_roles', 'staff_roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_number' => 'required|exists:individual,member_number',
            'professional_role_id' => 'required|exists:professional_roles,id',
        ]);

        $individual = Individual::where('member_number', $request->member_number)->firstOrFail();
        // Attach the professional role to the individual if it doesn't exist
        $individual->professionalRoles()->syncWithoutDetaching([$request->professional_role_id]);
        // Clear cache to reflect the new data
        // Cache::flush();

        return redirect()->route('admin.staff-roles.index')->with('success', __('professional_roles.role_assigned_successfully'));
    }

    public function destroy($individualProfessionalRoleId)
    {
        $individualProfessionalRole = IndividualProfessionalRole::findOrFail($individualProfessionalRoleId);

        // Perform the deletion
        $individualProfessionalRole->delete();

        // Clear cache to reflect the new data
        // Cache::flush();

        return redirect()->route('admin.staff-roles.index')->with('success', __('professional_roles.role_removed_successfully'));
    }
}
