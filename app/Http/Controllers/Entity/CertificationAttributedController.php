<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\CertificationAttributedRequest;
use App\Models\Committee;
use App\Models\Sport;
use Domain\Certifications\Actions\CreateCertificationAttributedAction;
use Domain\Certifications\DataTransferObject\CertificationAttributedData;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CertificationAttributedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $certifications_attributed = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee', 'filterCommittee'),
                AllowedFilter::scope('filter_expiration_end', 'expirationAfter'),
                AllowedFilter::scope('filter_expiration_start', 'expirationBefore'),
                AllowedFilter::scope('filter_emission_end', 'emissionAfter'),
                AllowedFilter::scope('filter_emission_start', 'emissionBefore'),
                AllowedFilter::scope('filter_certification', 'certificationId'),
                AllowedFilter::scope('filter_federation', 'federation'),
                AllowedFilter::scope('filter_status', 'certificationAttributedStatus'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_director_code', 'director_code'),
                AllowedFilter::scope('filter_member_code', 'member_code'),
            ])
            ->with(['mainInstructor' => function ($query) {
                $query->select('individual.id', 'native_name');
            }])
            ->where('entity_id', Auth::user()->entities()->first()->id)
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends(request()->query());

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Canceled')],
        ];

        $certifications = Certification::select('id', 'name')->orderBy('name')->get();
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();

        // Retrieve the current committee filter from the query parameters
        $currentCommittee = $request->input('filter.committee');

        return view('web.entity.certification_attributed.index', compact('certifications_attributed', 'certifications', 'filter_status', 'sports', 'federations', 'currentCommittee'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View|RedirectResponse
    {
        // Extract the committee code from the query string
        $committeeCode = $request->input('filter.committee');
        // Validate Committee
        $committee = Committee::where('code', $committeeCode)->first();
        if (! $committee) {
            // Redirect back with an error message if the committee is invalid or missing
            return redirect()->route('entity.certification-attributed.index')->withErrors(['committee' => 'Invalid or missing committee filter.']);
        }

        $federationId = Auth::user()->entities()->first()->federations()->first()->id;

        $entityId = Auth::user()->entities()->firstOrFail()->id;

        return view('web.entity.certification_attributed.create', compact('federationId', 'entityId'));
    }

    /**
     * Show the form for creating a new resource (Wizard).
     */
    public function createWizard(Request $request): View|RedirectResponse
    {
        // Extract the committee code from the query string
        $committeeCode = $request->input('filter.committee');
        // Validate Committee
        $committee = Committee::where('code', $committeeCode)->first();
        if (! $committee) {
            // Redirect back with an error message if the committee is invalid or missing
            return redirect()->route('entity.certification-attributed.index')->withErrors(['committee' => __('certifications.refresh_to_update_form')]);
        }

        // The wizard component will handle fetching its own necessary data based on the authenticated user and committee code.
        // We just need to pass the committee_code to the view that hosts the wizard.
        return view('web.entity.certification_attributed.wizard.create', compact('committeeCode'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {

        $certification_attributed = Cache::remember("certification_attributed_{$id}", 60, function () use ($id) {
            return CertificationAttributed::select(
                'id',
                'certification_id',
                'individual_id',
                'federation_id',
                'status_class',
                'international_code',
                'activator_id',
                'activator_type',
                'activated_at',
                'created_at',
                'current_term_ends_at',
                'entity_id',
                'national_code',
                'holder_name'
            )->with([
                'certification:id,name,committee_id,certification_view',
                'individual:id,name,surname,member_code,qrcode_path',
                'individual.media',
                'entity',
                'activator:id,name',
                'mainInstructor',
                'federation:id,member_code,country_id,name',
                'federation.country:id,name,iso,ioc',
            ])
                ->where('id', $id)
                ->firstOrFail();
        });

        $main_instructor = $certification_attributed->mainInstructor()->first();
        $assistants = $certification_attributed->assistantInstructors()->get();

        return view('web.entity.certification_attributed.show', [
            'certification' => $certification_attributed,
            'mainInstructor' => $main_instructor,
            'assistants' => $assistants,
            'showInstructorInfo' => ! empty($main_instructor) || ! empty($assistants),
        ]);
    }

    public function store(
        CertificationAttributedRequest $request,
        CreateCertificationAttributedAction $saveAction
    ): RedirectResponse {

        try {
            DB::beginTransaction();
            $save = $saveAction(CertificationAttributedData::fromArray($request), 'entity');
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return back()->with('error', 'There was a problem while creating this record: ' . $ex->getMessage());
        }

        $message = 'Certification attributed with success.';
        foreach ($save['individualsWithTheCertification'] as $key => $individual) {

            if ($key == 0) {
                $message .= ' This individuals already has this certifications: ';
            }

            $message .= $individual;
            if (count($save['individualsWithTheCertification']) > $key + 1) {
                $message .= ', ';
            }
        }

        return redirect()->back()->with('success', $message);
    }
}
