<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LicenseRequesterModelsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\LicenseRequest;
use App\Models\Committee;
use App\Models\Role;
use App\Models\Sport;
use Domain\Certifications\Models\Certification;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Actions\CreateLicenseAction;
use Domain\Licenses\Actions\DeleteLicenseAction;
use Domain\Licenses\Actions\EditLicenseAction;
use Domain\Licenses\DataTransferObject\LicenseData;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $licenses = QueryBuilder::for(License::class)
            ->withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_committee'),
                AllowedFilter::scope('filter_sport'),
                AllowedFilter::scope('filter_type'),
            ])
            ->with(['committee', 'type', 'federations'])
            ->paginate()
            ->appends(request()->query());

        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $types = LicenseType::select('id', 'name')->orderBy('name')->get();

        return view('web.admin.license.index', compact('licenses', 'committees', 'sports', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $licenseTypes = LicenseType::all();
        $professionalRoles = ProfessionalRole::all();
        $sports = Sport::all();
        $intervalUnit = config('enum.interval_unit');

        $requesterModels = LicenseRequesterModelsEnum::toArray();
        $certifications = Certification::select('id', 'name', 'acronym')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $federations = Federation::select('id', 'name', 'is_default_federation')
            ->orderBy('is_default_federation', 'desc')
            ->orderBy('name')
            ->get();

        $license = new License;

        return view('web.admin.license.create_tabbed', compact(
            'license',
            'committees',
            'licenseTypes',
            'professionalRoles',
            'sports',
            'intervalUnit',
            'requesterModels',
            'certifications',
            'roles',
            'federations'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LicenseRequest $licenseRequest, CreateLicenseAction $createLicenseAction): RedirectResponse
    {

        try {
            $license = $createLicenseAction(LicenseData::fromArray($licenseRequest->validated()));

            if (! empty($license)) {
                return redirect(route('admin.license.index'))->with('success', 'Record created with success.');
            } else {
                Log::error('Certification wasnt created: ' . json_encode($licenseRequest->validated()));

                return back()->with('error', 'Error creating this record.');
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error creating this record, please contact the administrator.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|RedirectResponse
    {
        // Eager-load media along with the license, required certifications, roles, and federations
        $license = License::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
            ->with(['media', 'requiredCertifications', 'roles', 'federations', 'sports'])
            ->find($id);

        if (! $license) {
            // Handle case where license is not found, maybe redirect back with error
            return redirect()->route('admin.license.index')->with('error', 'License not found.');
        }

        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $licenseTypes = LicenseType::all();
        $professionalRoles = ProfessionalRole::all();
        $sports = Sport::all();
        $intervalUnit = config('enum.interval_unit');
        $requesterModels = LicenseRequesterModelsEnum::toArray();
        $certifications = Certification::select('id', 'name', 'acronym')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $federations = Federation::select('id', 'name', 'is_default_federation')
            ->orderBy('is_default_federation', 'desc')
            ->orderBy('name')
            ->get();

        return view('web.admin.license.edit_tabbed', compact(
            'license',
            'committees',
            'licenseTypes',
            'professionalRoles',
            'sports',
            'intervalUnit',
            'requesterModels',
            'certifications',
            'roles',
            'federations'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LicenseRequest $licenseRequest, int $id, EditLicenseAction $editLicenseAction): RedirectResponse
    {

        try {
            $updated = $editLicenseAction(LicenseData::fromArray($licenseRequest->validated()), $id);

            if ($updated) {
                return redirect(route('admin.license.index', $id))->with('success', 'License updated with success.');
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error updating the license, please contact the please contact support.');
        }

        Log::error('Could not update license: ' . $id . ' - ' . json_encode($licenseRequest->validated()));

        return back()->with('error', 'Error updating a certification.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id, DeleteLicenseAction $deleteLicenseAction): RedirectResponse
    {
        try {
            $deleted = $deleteLicenseAction($id);

            if ($deleted) {
                return back()->with('success', 'License deleted with success.');
            } else {
                Log::error('License don\'t was deleted but there is no errors.');

                return back()->with('error', "The license hasn't been deleted.");
            }
        } catch (Exception $ex) {
            // This certification is referenced in another table.
            if ($ex->getCode() === 801) {
                return back()->with('error', $ex->getMessage());
            } else {
                Log::error($ex->getCode() . ': ' . $ex->getMessage());

                return back()->with('error', 'Error deleting the license, please contact the please contact the administration.');
            }
        }
    }
}
