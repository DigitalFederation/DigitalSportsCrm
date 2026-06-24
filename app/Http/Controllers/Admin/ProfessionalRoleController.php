<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProfessionalRoleController extends Controller
{
    protected array $roleTypes = [
        'ATHLETE',
        'COACH',
        'TECHNICAL_OFFICIAL',
        'INSTRUCTOR',
        'LEADER',
        'DIVER',
        'STAFF',
        'DIVINGPROFESSIONAL',
        'FEDERATION_STAFF',
    ];

    public function index(Request $request): View
    {
        $professionalRoles = QueryBuilder::for(ProfessionalRole::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('role'),
                AllowedFilter::exact('committee_id'),
            ])
            ->with('committee')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(25)
            ->appends($request->query());

        $committees = Committee::orderBy('name')->get();

        return view('web.admin.professional_roles.index', [
            'professionalRoles' => $professionalRoles,
            'roleTypes' => $this->roleTypes,
            'committees' => $committees,
        ]);
    }

    public function create(): View
    {
        $committees = Committee::orderBy('name')->get();

        return view('web.admin.professional_roles.create', [
            'roleTypes' => $this->roleTypes,
            'committees' => $committees,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:professional_roles,code'],
            'role' => ['required', 'string', Rule::in($this->roleTypes)],
            'committee_id' => ['nullable', 'exists:committee,id'],
        ]);

        ProfessionalRole::create($validated);

        return redirect()
            ->route('admin.professional-roles.index')
            ->with('success', __('professional_roles.created_successfully'));
    }

    public function edit(ProfessionalRole $professionalRole): View
    {
        $committees = Committee::orderBy('name')->get();

        return view('web.admin.professional_roles.edit', [
            'professionalRole' => $professionalRole,
            'roleTypes' => $this->roleTypes,
            'committees' => $committees,
        ]);
    }

    public function update(Request $request, ProfessionalRole $professionalRole): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('professional_roles', 'code')->ignore($professionalRole->id)],
            'role' => ['required', 'string', Rule::in($this->roleTypes)],
            'committee_id' => ['nullable', 'exists:committee,id'],
        ]);

        $professionalRole->update($validated);

        return redirect()
            ->route('admin.professional-roles.index')
            ->with('success', __('professional_roles.updated_successfully'));
    }

    public function destroy(ProfessionalRole $professionalRole): RedirectResponse
    {
        try {
            // Check for related records (bypass global scopes to catch all FK references)
            if ($professionalRole->individuals()->exists()) {
                return redirect()
                    ->route('admin.professional-roles.index')
                    ->with('error', __('professional_roles.cannot_delete_has_individuals'));
            }

            if ($professionalRole->certifications()->withoutGlobalScopes()->exists()) {
                return redirect()
                    ->route('admin.professional-roles.index')
                    ->with('error', __('professional_roles.cannot_delete_has_certifications'));
            }

            if ($professionalRole->licenses()->withoutGlobalScopes()->exists()) {
                return redirect()
                    ->route('admin.professional-roles.index')
                    ->with('error', __('professional_roles.cannot_delete_has_licenses'));
            }

            $professionalRole->delete();

            return redirect()
                ->route('admin.professional-roles.index')
                ->with('success', __('professional_roles.deleted_successfully'));
        } catch (\Exception $e) {
            Log::error('Failed to delete professional role: ' . $e->getMessage());

            return redirect()
                ->route('admin.professional-roles.index')
                ->with('error', __('professional_roles.delete_failed'));
        }
    }
}
