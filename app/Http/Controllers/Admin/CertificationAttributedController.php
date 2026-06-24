<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Common\BaseCertificationAttributedController;
use App\Http\Requests\CertificationAttributedRequest;
use App\Models\GeoZone;
use App\Models\Sport;
use Domain\Certifications\Actions\ActivateCertificationAttributedAction;
use Domain\Certifications\Actions\CreateCertificationAttributedAction;
use Domain\Certifications\Actions\DeleteCertificationAttributedAction;
use Domain\Certifications\DataTransferObject\CertificationAttributedData;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\ExpiredCertificationAttributedState;
use Domain\Certifications\States\ProvisionalCertificationAttributedState;
use Domain\Certifications\States\SuspendedCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CertificationAttributedController extends BaseCertificationAttributedController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
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
                AllowedFilter::scope('filter_entity', 'entity'),
                AllowedFilter::scope('filter_status', 'certificationAttributedStatus'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_director_member_number', 'filterDirectorMemberNumber'),
                AllowedFilter::scope('filter_member_code', 'member_code'),
                AllowedFilter::scope('filter_zone'),
                AllowedFilter::scope('filter_individual_member_code', 'filterIndividualMemberCode'),
                AllowedFilter::scope('filter_payment_status', 'filterPaymentStatus'),
                AllowedFilter::scope('filter_student_name', 'filterStudentName'),
                AllowedFilter::scope('filter_student_surname', 'filterStudentSurname'),
            ])
            ->withPaymentStatus()
            ->with('certification.license.sport', 'federation.country', 'individual', 'entity', 'mainInstructor')
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends(request()->query());

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Canceled')],
            'waiting_director' => ['id' => 'waiting_director', 'name' => __('Waiting Director')],
            'waiting_nf' => ['id' => 'waiting_nf', 'name' => __('Waiting NF')],
            'expired' => ['id' => 'expired', 'name' => __('Expired')],
            'provisional' => ['id' => 'provisional', 'name' => __('Provisional')],
            'rejected' => ['id' => 'rejected', 'name' => __('Rejected')],
            'suspended' => ['id' => 'suspended', 'name' => __('Suspended')],
        ];

        $committeeFilter = strtoupper(request()->query('filter')['committee'] ?? '');
        $issuedStates = [
            ActiveCertificationAttributedState::class,
            ExpiredCertificationAttributedState::class,
            SuspendedCertificationAttributedState::class,
            ProvisionalCertificationAttributedState::class,
        ];

        $certifications = Certification::select('id', 'name')
            ->whereHas('certificationsAttributed', fn ($q) => $q->whereIn('status_class', $issuedStates))
            ->when($committeeFilter, function ($query) use ($committeeFilter) {
                // For diving committee, include both DIVING and SCIENTIFIC committees
                if ($committeeFilter === 'DIVING') {
                    return $query->whereHas('committee', function ($q) {
                        $q->whereIn('code', ['DIVING', 'SCIENTIFIC']);
                    });
                }

                return $query->whereHas('committee', function ($q) use ($committeeFilter) {
                    $q->where('code', $committeeFilter);
                });
            })
            ->orderBy('name')
            ->get();
        $federations = Federation::select('id', 'name', 'member_code')->orderBy('name')->get();
        $entities = Entity::select('id', 'name')
            ->whereHas('certifications', fn ($q) => $q->whereIn('status_class', $issuedStates))
            ->orderBy('name')
            ->get();

        $sports = Sport::select('id', 'name')
            ->whereHas('licenses.certifications.certificationsAttributed', fn ($q) => $q->whereIn('status_class', $issuedStates))
            ->orderBy('name')
            ->get();
        $cmas_zones = GeoZone::select('id', 'name')->orderBy('name')->get();

        $filter_payment_status = [
            'paid' => ['id' => 'paid', 'name' => __('certifications.index.table.payment_status_paid')],
            'pending_payment' => ['id' => 'pending_payment', 'name' => __('certifications.index.table.payment_status_pending_payment')],
            'no_document' => ['id' => 'no_document', 'name' => __('certifications.index.table.payment_status_no_document')],
        ];

        return view('web.admin.certification_attributed.index', compact('certifications_attributed', 'certifications', 'filter_status', 'federations', 'entities', 'sports', 'cmas_zones', 'filter_payment_status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $certifications = Certification::select('id', 'name')->orderBy('name')->get();

        return view('web.admin.certification_attributed.create', compact('federations', 'certifications'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        CertificationAttributedRequest $request,
        CreateCertificationAttributedAction $saveAction
    ): RedirectResponse {
        try {
            DB::beginTransaction();

            // Transform the request data to match expected format
            $requestData = $request->all();

            // Transform individual array structure to individual_ids
            if (isset($requestData['individual']['id'])) {
                $requestData['individual_ids'] = $requestData['individual']['id'];
            }

            // Transform instructor_id to director_instructor_id
            if (isset($requestData['instructor_id'])) {
                $requestData['director_instructor_id'] = $requestData['instructor_id'];
            }

            // Transform assistant array to assistant_instructor_ids
            if (isset($requestData['assistant'])) {
                $requestData['assistant_instructor_ids'] = $requestData['assistant'];
            }

            $save = $saveAction(CertificationAttributedData::fromArray($requestData));
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return back()->with('error', 'There was a problem while creating this record: ' . $ex->getMessage());
        }

        $message = 'Certification attributed with success.';
        foreach ($save['individualsWithTheCertification'] as $key => $individual) {

            if ($key == 0) {
                $message .= ' This individual already has this certification(s): ';
            }

            $message .= $individual;
            if (count($save['individualsWithTheCertification']) > $key + 1) {
                $message .= ', ';
            }
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {

        try {
            $certificationAttributed = CertificationAttributed::with('certification', 'individual', 'federation', 'entity')->findOrFail($id);

            return view('web.admin.certification_attributed.edit', compact('certificationAttributed'));
        } catch (\Exception $exception) {
            return back()->with('error', 'Error finding certification with ID: ' . $id);
        }
    }

    public function update(Request $request, $id)
    {

        $validatedData = $request->validate([
            'national_code' => 'nullable|string|max:255',
            'current_term_starts_at' => 'nullable|date',
            'current_term_ends_at' => 'nullable|date|after_or_equal:current_term_starts_at',
            'status_class' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $certificationAttributed = CertificationAttributed::findOrFail($id);
            $certificationAttributed->national_code = $validatedData['national_code'];
            $certificationAttributed->current_term_starts_at = $validatedData['current_term_starts_at'];
            $certificationAttributed->current_term_ends_at = $validatedData['current_term_ends_at'];
            $certificationAttributed->status_class = $validatedData['status_class'];
            $certificationAttributed->notes = $validatedData['notes'];
            // Detect changes for activity logging
            $changes = $certificationAttributed->getDirty();

            $certificationAttributed->save();

            // Log activity
            activity()
                ->performedOn($certificationAttributed)
                ->causedBy(Auth::user())
                ->withProperties(['changes' => $changes])
                ->log(__('certifications.edit.activity_log'));

            return redirect()->route('admin.certification-attributed.show', $id)->with('success', __('certifications.edit.updated_success'));
        } catch (\Exception $exception) {
            // Handle the error properly
            return back()->withInput()->with('error', 'Error updating certification: ' . $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {
        $certification_attributed = Cache::remember("certification_attributed_{$id}", 1, function () use ($id) {
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
                'current_term_starts_at',
                'current_term_ends_at',
                'entity_id',
                'national_code',
                'holder_name',
                'notes'
            )
                ->with([
                    'certification:id,name,committee_id,certification_view',
                    'certification.committee',
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

        return view('web.admin.certification_attributed.show', [
            'certification' => $certification_attributed,
            'mainInstructor' => $main_instructor,
            'assistants' => $assistants,
            'showInstructorInfo' => ! empty($main_instructor) || ! empty($assistants),
        ]);
    }

    /**
     * Activate the Certification based on permission rules
     * Permissions: international, FEDERATION, INDIVIDUAL
     */
    public function activate(
        Request $request,
        ActivateCertificationAttributedAction $activateCertification
    ): RedirectResponse {
        // Invalidate the cache
        Cache::forget("certification_attributed_{$request->id}");
        try {
            DB::beginTransaction();
            $certification_attributed = CertificationAttributed::where('id', $request->id)->sharedLock()->first();
            $activateCertification($certification_attributed);
            DB::commit();

            return redirect(route('admin.certification-attributed.show', $request->id))->with('success', 'Certification activated with success.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getCode() . ': ' . $exception->getMessage());

            return back()->with('error', 'Error validating certification' . $exception->getMessage());
        }
    }

    public function suspend(
        Request $request,
    ): RedirectResponse {

        // Invalidate the cache
        Cache::forget("certification_attributed_{$request->id}");

        try {
            DB::beginTransaction();

            $certification_attributed = CertificationAttributed::where('id', $request->id)->sharedLock()->first();
            $certification_attributed->status_class = SuspendedCertificationAttributedState::class;
            $certification_attributed->save();

            DB::commit();

            activity('CertificationAttributed')
                ->performedOn($certification_attributed)
                ->event('suspend')
                ->withProperties((array) $certification_attributed->toArray())
                ->log('Certification suspended: ' . $certification_attributed->certification->name . ' to ' . $certification_attributed->holder_name);

            return back()->with('success', 'Certification suspended with success.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getCode() . ': ' . $exception->getMessage());

            return back()->with('error', 'Error validating certification');
        }
    }

    public function unsuspend(
        Request $request,
    ): RedirectResponse {
        // Invalidate the cache
        Cache::forget("certification_attributed_{$request->id}");

        try {
            DB::beginTransaction();

            $certification_attributed = CertificationAttributed::where('id', $request->id)->sharedLock()->first();

            // If current_term_ends_at is not null and already expired, do not allow to unsuspend
            $expirationDate = Carbon::parse($certification_attributed->current_term_ends_at);
            if ($certification_attributed->current_term_ends_at && $expirationDate->isPast()) {
                return back()->withErrors('Certification is expired, cannot be unsuspended.');
            }

            $certification_attributed->status_class = ActiveCertificationAttributedState::class;
            $certification_attributed->save();

            DB::commit();

            activity('CertificationAttributed')
                ->performedOn($certification_attributed)
                ->event('unsuspend')
                ->withProperties((array) $certification_attributed->toArray())
                ->log('Certification unsuspended: ' . $certification_attributed->certification->name . ' of ' . $certification_attributed->holder_name);

            return back()->with('success', 'Certification unsuspended with success.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getCode() . ': ' . $exception->getMessage());

            return back()->with('error', 'Error validating certification');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, DeleteCertificationAttributedAction $deleteAction): RedirectResponse
    {
        try {
            $deleted = $deleteAction($id);

            if ($deleted) {
                return back()->with('success', 'Certification unassigned with success.');
            } else {
                Log::error('Certification don\'t was unassigned but there is no errors.');

                return back()->with('error', "The certification hasn't been unassigned.");
            }
        } catch (Exception $ex) {
            // This certification is referenced in another table.
            if ($ex->getCode() === 801) {
                return back()->with('error', $ex->getMessage());
            } else {
                Log::error($ex->getCode() . ': ' . $ex->getMessage());

                return back()->with('error', 'Error assigning the certification, please contact the please contact the administration.');
            }
        }
    }
}
