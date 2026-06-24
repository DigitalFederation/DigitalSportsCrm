<?php

namespace App\Http\Controllers\Federation;

use App\Enums\CommitteeCodeEnum;
use App\Http\Controllers\Common\BaseCertificationAttributedController;
use App\Models\Sport;
use Domain\Certifications\Actions\ActivateCertificationAttributedByFederationAction;
use Domain\Certifications\Actions\DeleteCertificationAttributedAction;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\CanceledCertificationAttributedState;
use Domain\Certifications\States\RejectedCertificationAttributedState;
use Domain\Certifications\States\SuspendedCertificationAttributedState;
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
        // Initialize filter array from request
        $filter = request()->input('filter', []);

        // Retrieve IDs of federations to show
        $currentFederation = Auth::user()->federations()->first();
        $currentFederationId = $currentFederation->id;
        $isMainFederation = $currentFederation->is_default_federation;

        // Main federation can see all certifications, others are filtered by federation
        $federationIds = [];
        if (! $isMainFederation) {
            $federationIds = [$currentFederationId];

            // Include child federation IDs
            $childFederationIds = Federation::where('parent_id', $currentFederationId)
                ->pluck('id')
                ->toArray();
            $federationIds = array_merge($federationIds, $childFederationIds);

            // For local federations, also include parent (main) federation certifications
            if ($currentFederation->parent_id) {
                $federationIds[] = $currentFederation->parent_id;
            }
        }

        // Get the committee filter to determine which certifications to show
        $committeeFilter = $filter['committee'] ?? 'sport';

        // Get committees this federation can manage
        $allowedCommitteeIds = $currentFederation->committees()
            ->pluck('committee.id')
            ->toArray();

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
                AllowedFilter::scope('filter_student_name', 'filterStudentName'),
                AllowedFilter::scope('filter_student_surname', 'filterStudentSurname'),
            ])
            // Main federation sees all; others are filtered by federation and committee
            ->when(! $isMainFederation, function ($query) use ($federationIds, $allowedCommitteeIds) {
                $query->whereIn('federation_id', $federationIds);

                if (empty($allowedCommitteeIds)) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereHas('certification', function ($q) use ($allowedCommitteeIds) {
                    $q->whereIn('committee_id', $allowedCommitteeIds);
                });
            })
            ->withPaymentStatus()
            ->with('certification.license.sport', 'certification.committee', 'federation.country', 'mainInstructor', 'entity', 'individual')
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends(request()->query());

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('certifications.details.states.active')],
            'pending' => ['id' => 'pending', 'name' => __('certifications.details.states.pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('certifications.details.states.canceled')],
            'waiting_director' => ['id' => 'waiting_director', 'name' => __('certifications.details.states.director_approval')],
            'waiting_nf' => ['id' => 'waiting_nf', 'name' => __('certifications.details.states.director_approved')],
            'expired' => ['id' => 'expired', 'name' => __('certifications.details.states.expired')],
            'provisional' => ['id' => 'provisional', 'name' => __('certifications.details.states.provisional')],
            'rejected' => ['id' => 'rejected', 'name' => __('certifications.details.states.rejected')],
            'suspended' => ['id' => 'suspended', 'name' => __('certifications.details.states.suspended')],
        ];

        // Show certifications based on the committee filter
        // Diving committee includes both DIVING and SCIENTIFIC certifications
        $committeeCodes = CommitteeCodeEnum::certificationFilterValues($committeeFilter);

        // Only list certifications that actually have attributions visible to this federation
        $certifications = Certification::select('id', 'name')
            ->whereHas('committee', function ($q) use ($committeeCodes) {
                $q->whereIn('code', $committeeCodes);
            })
            ->when(! $isMainFederation, function ($query) use ($allowedCommitteeIds) {
                if (empty($allowedCommitteeIds)) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereIn('committee_id', $allowedCommitteeIds);
            })
            ->whereHas('certificationsAttributed', function ($q) use ($isMainFederation, $federationIds) {
                if (! $isMainFederation) {
                    $q->whereIn('federation_id', $federationIds);
                }
            })
            ->orderBy('name')
            ->get();
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();

        return view('web.federation.certification_attributed.index', compact(
            'certifications_attributed',
            'certifications',
            'filter_status',
            'sports',
            'federations',
            'filter'
        ));
    }

    /**
     * Show the form for creating a new resource (Wizard).
     */
    public function createWizard(): View
    {
        return view('web.federation.certification_attributed.wizard.create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $federationId = Auth::user()->federations()->firstOrFail()->id;

        return view('web.federation.certification_attributed.create', compact('federationId'));
    }

    /**
     * Activate the Certification based on permission rules
     * Permissions: international, FEDERATION, INDIVIDUAL
     */
    public function activate(
        Request $request,
        ActivateCertificationAttributedByFederationAction $activateAction
    ): RedirectResponse {

        // Invalidate the cache
        Cache::forget("certification_attributed_{$request->id}");

        try {
            DB::beginTransaction();
            $certification_attributed = CertificationAttributed::where('id', $request->id)->sharedLock()->first();
            if (empty($certification_attributed->national_code)) {
                return back()->with('error', 'National code is required to validate this certification.');
            }

            $federation = Auth::user()->federations()->first();
            if (! $federation || ! $federation->canIssueCertifications()) {
                throw new Exception(__('federation.cannot_issue_certifications'));
            }

            $activateAction($certification_attributed);
            DB::commit();

            return back()->with('success', 'Certification activated with success.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function cancel(
        Request $request,
    ): RedirectResponse {

        // Invalidate the cache
        Cache::forget("certification_attributed_{$request->id}");

        try {
            DB::beginTransaction();

            $certification_attributed = CertificationAttributed::where('id', $request->id)->sharedLock()->first();
            $certification_attributed->status_class = CanceledCertificationAttributedState::class;
            $certification_attributed->save();

            DB::commit();

            activity('CertificationAttributed')
                ->performedOn($certification_attributed)
                ->event('suspend')
                ->withProperties((array) $certification_attributed->toArray())
                ->log('Certification suspended: ' . $certification_attributed->certification->name . ' to ' . $certification_attributed->holder_name);
            // Invalidate the cache
            Cache::forget("certification_attributed_{$request->id}");

            return back()->with('success', 'Certification suspended with success.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getCode() . ': ' . $exception->getMessage());

            return back()->with('error', 'Error validating certification');
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

    public function reject(
        Request $request,
    ): RedirectResponse {

        // Invalidate the cache
        Cache::forget("certification_attributed_{$request->id}");

        try {
            DB::beginTransaction();

            $certification_attributed = CertificationAttributed::where('id', $request->id)->sharedLock()->first();
            $certification_attributed->status_class = RejectedCertificationAttributedState::class;
            $certification_attributed->save();

            DB::commit();

            activity('CertificationAttributed')
                ->performedOn($certification_attributed)
                ->event('canceled')
                ->withProperties((array) $certification_attributed->toArray())
                ->log('Certification rejected: ' . $certification_attributed->certification->name . ' to ' . $certification_attributed->holder_name);

            return back()->with('success', 'Certification canceled with success.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getCode() . ': ' . $exception->getMessage());

            return back()->with('error', 'Error validating certification');
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
                'holder_name'
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

        return view('web.federation.certification_attributed.show', [
            'certification' => $certification_attributed,
            'mainInstructor' => $main_instructor,
            'assistants' => $assistants,
            'showInstructorInfo' => ! empty($main_instructor) || ! empty($assistants),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View|RedirectResponse
    {
        $user = Auth::user();
        $federation = $user->federations()->first();
        $certificationAttributed = CertificationAttributed::with('certification.committee', 'individual', 'federation', 'entity')->findOrFail($id);

        // Only allow editing if the certification belongs to the user's federation or its children
        $allowedFederationIds = Federation::where('parent_id', $federation->id)->pluck('id')->toArray();
        $allowedFederationIds[] = $federation->id;
        if (! in_array($certificationAttributed->federation_id, $allowedFederationIds)) {
            abort(403, __('You are not authorized to edit this certification.'));
        }

        // Check if federation can manage the certification's committee
        if ($certificationAttributed->certification?->committee) {
            $committee = $certificationAttributed->certification->committee;
            if (! $federation->canManageCommittee($committee)) {
                abort(403, __('federation.cannot_manage_committee'));
            }
        }

        return view('web.federation.certification_attributed.edit', compact('certificationAttributed'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $federation = $user->federations()->first();
        $certificationAttributed = CertificationAttributed::with('certification.committee')->findOrFail($id);

        // Only allow updating if the certification belongs to the user's federation or its children
        $allowedFederationIds = Federation::where('parent_id', $federation->id)->pluck('id')->toArray();
        $allowedFederationIds[] = $federation->id;
        if (! in_array($certificationAttributed->federation_id, $allowedFederationIds)) {
            abort(403, __('You are not authorized to update this certification.'));
        }

        // Check if federation can manage the certification's committee
        if ($certificationAttributed->certification?->committee) {
            $committee = $certificationAttributed->certification->committee;
            if (! $federation->canManageCommittee($committee)) {
                abort(403, __('federation.cannot_manage_committee'));
            }
        }

        $validatedData = $request->validate([
            'national_code' => 'nullable|string|max:255',
            'current_term_starts_at' => 'nullable|date',
            'current_term_ends_at' => 'nullable|date|after_or_equal:current_term_starts_at',
            'notes' => 'nullable|string|max:1000',
        ]);

        $certificationAttributed->national_code = $validatedData['national_code'];
        $certificationAttributed->current_term_starts_at = $validatedData['current_term_starts_at'];
        $certificationAttributed->current_term_ends_at = $validatedData['current_term_ends_at'];
        $certificationAttributed->notes = $validatedData['notes'];
        $changes = $certificationAttributed->getDirty();
        $certificationAttributed->save();

        activity()
            ->performedOn($certificationAttributed)
            ->causedBy($user)
            ->withProperties(['changes' => $changes])
            ->log('Certification updated by Federation admin');

        return redirect()->route('federation.certification-attributed.show', $id)->with('success', __('Certification updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        string $id,
        DeleteCertificationAttributedAction $deleteAction
    ): RedirectResponse {
        try {
            $certification = CertificationAttributed::findOrFail($id);

            if (! $certification->canBeDeleted()) {
                return back()->with('error', 'This certification cannot be deleted.');
            }

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

                return back()->with('error', 'Error assigning the certification, please contact the administration.');
            }
        }
    }
}
