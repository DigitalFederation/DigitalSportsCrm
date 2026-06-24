<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DivingCertificationsController extends Controller
{
    public function index(): View
    {
        $individual = auth()->user()->individual;

        $certifications = QueryBuilder::for(DivingProfessionalCertification::class)
            ->allowedFilters([
                AllowedFilter::exact('certification_system')->ignore(null),
                AllowedFilter::exact('status_class')->ignore(null),
                AllowedFilter::scope('active')->ignore(null),
            ])
            ->where('individual_id', $individual->id)
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends(request()->query());

        $certificationSystems = array_combine(config('diving.certification_systems'), config('diving.certification_systems'));

        $statusOptions = [
            PendingValidationDivingCertificationState::class => __('main.pending_validation'),
            ActiveDivingCertificationState::class => __('main.active'),
        ];

        return view('web.individual.diving_certifications.index', compact(
            'certifications',
            'certificationSystems',
            'statusOptions'
        ));
    }

    public function create(): View
    {
        $certificationSystems = array_combine(config('diving.certification_systems'), config('diving.certification_systems'));

        return view('web.individual.diving_certifications.create', compact('certificationSystems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'certification_name' => 'required|string|max:255',
            'certification_system' => 'required|in:'.implode(',', config('diving.certification_systems')),
            'certification_number' => 'required|string|max:255',
            'national_certification_level' => 'required|in:diver_level_3,instructor_level_1,instructor_level_2,instructor_level_3,first_aid_bls_oxygen,compressor_operator',
            'issue_date' => 'required|date|before_or_equal:today',
            'expiration_date' => 'nullable|date|after:issue_date',
            'certificate_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $individual = auth()->user()->individual;

            if (! $individual) {
                throw new \Exception('Individual profile not found');
            }

            $certification = DivingProfessionalCertification::create([
                'individual_id' => $individual->id,
                'certification_name' => $validated['certification_name'],
                'certification_system' => $validated['certification_system'],
                'document_type' => 'other', // Default to 'other' since field is removed from form
                'certification_level' => $validated['national_certification_level'],
                'certification_number' => $validated['certification_number'],
                'national_equivalency' => $validated['national_certification_level'],
                'issue_date' => $validated['issue_date'],
                'expiration_date' => $validated['expiration_date'] ?? null,
                'status_class' => PendingValidationDivingCertificationState::class,
            ]);

            if ($request->hasFile('certificate_document')) {
                $certification->addMediaFromRequest('certificate_document')
                    ->toMediaCollection('certificate_documents');
            }

            DB::commit();

            activity('DivingCertification')
                ->causedBy(auth()->user())
                ->performedOn($certification)
                ->event('created')
                ->log('Diving certification submitted for validation');

            return redirect()->route('individual.diving_certifications.index')
                ->with('success', __('Certification submitted successfully. It will be reviewed by the federation.'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create diving certification: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', __('Failed to submit certification. Please try again.'));
        }
    }

    public function show(DivingProfessionalCertification $certification): View
    {
        $this->authorize('view', $certification);

        $certification->load(['individual.country', 'validatedBy', 'media']);

        return view('web.individual.diving_certifications.show', compact('certification'));
    }

    public function edit(DivingProfessionalCertification $certification): View|RedirectResponse
    {
        $this->authorize('update', $certification);

        if (! $certification->canBeValidated()) {
            return redirect()->route('individual.diving_certifications.show', $certification)
                ->with('error', __('This certification cannot be edited in its current state.'));
        }

        $certificationSystems = array_combine(config('diving.certification_systems'), config('diving.certification_systems'));

        return view('web.individual.diving_certifications.edit', compact('certification', 'certificationSystems'));
    }

    public function update(Request $request, DivingProfessionalCertification $certification): RedirectResponse
    {
        $this->authorize('update', $certification);

        if (! $certification->canBeValidated()) {
            return back()->with('error', __('This certification cannot be updated in its current state.'));
        }

        $validated = $request->validate([
            'certification_name' => 'required|string|max:255',
            'certification_system' => 'required|in:'.implode(',', config('diving.certification_systems')),
            'certification_level' => 'required|string|max:255',
            'certification_number' => 'required|string|max:255',
            'national_equivalency' => 'nullable|string|max:255',
            'national_certification_level' => 'nullable|in:diver_level_3,instructor_level_1,instructor_level_2,instructor_level_3,first_aid_bls_oxygen,compressor_operator',
            'issue_date' => 'required|date|before_or_equal:today',
            'expiration_date' => 'nullable|date|after:issue_date',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // Keep existing document_type if not provided
            $updateData = $validated;
            if (! isset($updateData['document_type'])) {
                $updateData['document_type'] = $certification->document_type ?: 'other';
            }

            $certification->update($updateData);

            if ($request->hasFile('certificate')) {
                $certification->clearMediaCollection('certificate');
                $certification->addMediaFromRequest('certificate')
                    ->toMediaCollection('certificate');
            }

            DB::commit();

            activity('DivingCertification')
                ->causedBy(auth()->user())
                ->performedOn($certification)
                ->event('updated')
                ->log('Diving certification updated');

            return redirect()->route('individual.diving_certifications.show', $certification)
                ->with('success', __('Certification updated successfully.'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update diving certification: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', __('Failed to update certification. Please try again.'));
        }
    }

    public function destroy(DivingProfessionalCertification $certification): RedirectResponse
    {
        $this->authorize('delete', $certification);

        if (! $certification->canBeValidated()) {
            return back()->with('error', __('This certification cannot be deleted in its current state.'));
        }

        try {
            $certification->delete();

            activity('DivingCertification')
                ->causedBy(auth()->user())
                ->performedOn($certification)
                ->event('deleted')
                ->log('Diving certification deleted');

            return redirect()->route('individual.diving_certifications.index')
                ->with('success', __('Certification deleted successfully.'));

        } catch (\Exception $e) {
            Log::error('Failed to delete diving certification: ' . $e->getMessage());

            return back()->with('error', __('Failed to delete certification. Please try again.'));
        }
    }
}
