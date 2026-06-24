<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CertificationCategoryEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CertificationRequest;
use App\Models\Committee;
use App\Models\Role;
use App\Models\Sport;
use Domain\Certifications\Actions\CreateCertificationAction;
use Domain\Certifications\Actions\DeleteCertificationAction;
use Domain\Certifications\Actions\EditCertificationAction;
use Domain\Certifications\Actions\UploadCertificationView;
use Domain\Certifications\DataTransferObject\CertificationData;
use Domain\Certifications\Models\Certification;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CertificationController extends Controller
{
    /**
     * Display diving/scientific certifications (server-side enforced).
     */
    public function indexDiving(): View
    {
        return $this->buildIndex(['diving', 'scientific'], 'diving');
    }

    /**
     * Display sport certifications (server-side enforced).
     */
    public function indexSport(): View
    {
        return $this->buildIndex(['sport'], 'sport');
    }

    /**
     * Legacy index - redirects to diving by default.
     */
    public function index(): View
    {
        $committeesRequest = ! empty(request()->query('filter')['committee']) ? explode(',', request()->query('filter')['committee']) : null;

        if (empty($committeesRequest)) {
            $committeesRequest = ['diving', 'scientific'];
        }

        $committeeType = in_array('sport', $committeesRequest) ? 'sport' : 'diving';

        return $this->buildIndex($committeesRequest, $committeeType);
    }

    /**
     * Build the index view for a given set of committees.
     */
    private function buildIndex(array $committeeCodes, string $committeeType): View
    {
        $certifications = QueryBuilder::for(Certification::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_committee', 'filterCommitteeId'),
                AllowedFilter::scope('filter_professional_role'),
                AllowedFilter::scope('filter_license'),
                AllowedFilter::scope('filter_sport'),
                AllowedFilter::scope('filter_available'),
            ])
            ->whereHas('committee', function ($query) use ($committeeCodes) {
                $query->whereIn('code', array_map('strtoupper', $committeeCodes));
            })
            ->with(['committee', 'professionalRole', 'parents', 'license.sport'])
            ->orderBy('name')
            ->paginate()
            ->appends(request()->query());

        $committees = Committee::whereIn('code', array_map('strtoupper', $committeeCodes))->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->committeeCode($committeeCodes)->orderBy('name')->get();
        $licenses = License::select('id', 'name')->whereHas('committee', function ($query) use ($committeeCodes) {
            $query->whereIn('code', array_map('strtoupper', $committeeCodes));
        })->orderBy('name')->get();

        $title = $committeeType === 'sport'
            ? __('certifications.titles.index_sport')
            : __('certifications.titles.index_diving');

        return view('web.admin.certification.index', compact(
            'certifications',
            'committees',
            'sports',
            'professional_roles',
            'licenses',
            'title',
            'committeeType'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $parents = Certification::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();
        $licenses = License::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();

        $roles = Role::orderBy('name')->get();

        $title = __('certifications.titles.create_default');
        // Fetch and sort certification categories based on their order
        $certification_categories = collect(CertificationCategoryEnum::cases())
            ->values();

        $certification = new Certification;

        return view('web.admin.certification.create', compact('committees', 'professional_roles', 'parents', 'licenses', 'certification', 'title', 'certification_categories', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CertificationRequest $certificationRequest, CreateCertificationAction $createCertificationAction, UploadCertificationView $uploadCertificationView): RedirectResponse
    {
        try {
            $certification = $createCertificationAction(CertificationData::fromArray($certificationRequest->validated()));

            if (request()->hasFile('certification_view')) {
                $uploadCertificationView(request()->file('certification_view'), $certification);
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode().': '.$ex->getMessage());

            return redirect()->back()->with('error', 'Error creating this record, please contact the administrator.');
        }

        $certification->load('committee');
        $committeeCode = strtolower($certification->committee?->code ?? 'diving');
        $redirectRoute = $committeeCode === 'sport'
            ? 'admin.certification.sport'
            : 'admin.certification.diving';

        return redirect(route($redirectRoute))->with('success', __('certifications.messages.created'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $parents = Certification::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();
        $licenses = License::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();

        $roles = Role::orderBy('name')->get();

        // Fetch and sort certification categories based on their order
        $certification_categories = collect(CertificationCategoryEnum::cases())
            ->values();

        $certification = Certification::with('roles')->find($id);

        return view('web.admin.certification.edit', compact('committees', 'professional_roles', 'licenses', 'parents', 'certification', 'certification_categories', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CertificationRequest $request, int $id, EditCertificationAction $editAction, UploadCertificationView $uploadCertificationView): RedirectResponse
    {
        try {
            $editAction(CertificationData::fromArray($request->validated()), $id);

            if (request()->hasFile('certification_view')) {
                $uploadCertificationView(request()->file('certification_view'), Certification::find($id));
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode().': '.$ex->getMessage());

            return redirect()->back()->with('error', 'Error updating data, please contact the administrator or try again later.');
        }

        return redirect()->back()->with('success', 'Certification data updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id, DeleteCertificationAction $deleteCertificationAction): RedirectResponse
    {
        try {
            // Get committee before deleting to know where to redirect
            $certification = Certification::with('committee')->find($id);
            $committeeCode = strtolower($certification?->committee?->code ?? 'diving');
            $redirectRoute = $committeeCode === 'sport'
                ? 'admin.certification.sport'
                : 'admin.certification.diving';

            $deleteCertificationAction($id);

            return redirect(route($redirectRoute))->with('success', __('certifications.messages.deleted'));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return redirect()->back()->with('error', $ex->getMessage());
        }
    }
}
