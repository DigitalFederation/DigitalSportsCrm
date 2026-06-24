<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\StreamsMediaFromStorage;
use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Diving\States\ExpiredDivingCertificationState;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Domain\Diving\States\RevokedDivingCertificationState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DivingProfessionalCertificationController extends Controller
{
    use StreamsMediaFromStorage;
    public function index(Request $request): View
    {
        $query = DivingProfessionalCertification::query()
            ->with(['individual.media', 'media'])
            ->latest();

        if ($request->input('filter.status')) {
            $query->whereState('status_class', $request->input('filter.status'));
        }

        if ($request->input('filter.system')) {
            $query->where('certification_system', $request->input('filter.system'));
        }

        if ($request->input('filter.name')) {
            $search = $request->input('filter.name');
            $query->where(function ($q) use ($search) {
                $q->whereHas('individual', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('member_number', 'like', "%{$search}%");
                })->orWhere('certification_number', 'like', "%{$search}%");
            });
        }

        $certifications = $query->paginate(20)->withQueryString();

        $statusOptions = [
            PendingValidationDivingCertificationState::class => __('diving.pending_validation'),
            ActiveDivingCertificationState::class => __('diving.active'),
            ExpiredDivingCertificationState::class => __('diving.expired'),
            RevokedDivingCertificationState::class => __('diving.revoked'),
        ];

        $systemOptions = array_combine(config('diving.certification_systems'), config('diving.certification_systems'));

        return view('web.admin.diving_professional_certifications.index', compact(
            'certifications',
            'statusOptions',
            'systemOptions'
        ));
    }

    public function show(DivingProfessionalCertification $certification): View
    {
        $certification->load(['individual.country', 'validatedBy', 'media']);

        return view('web.admin.diving_professional_certifications.show', compact('certification'));
    }

    public function approve(DivingProfessionalCertification $certification): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $certification->status_class = ActiveDivingCertificationState::class;
            $certification->validated_by = auth()->id();
            $certification->validated_at = now();
            $certification->save();

            DB::commit();

            return redirect()->route('admin.diving_professional_certifications.show', $certification)
                ->with('success', __('diving.certification_approved_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', __('diving.failed_to_approve_certification'));
        }
    }

    public function reject(Request $request, DivingProfessionalCertification $certification): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $certification->status_class = RevokedDivingCertificationState::class;
            $certification->validated_by = auth()->id();
            $certification->validated_at = now();
            $certification->rejection_reason = $request->reason;
            $certification->save();

            // TODO: Send notification to individual about rejection

            DB::commit();

            return redirect()->route('admin.diving_professional_certifications.show', $certification)
                ->with('success', __('diving.certification_rejected_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', __('diving.failed_to_reject_certification'));
        }
    }

    public function revoke(Request $request, DivingProfessionalCertification $certification): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $certification->status_class = RevokedDivingCertificationState::class;
            $certification->revocation_reason = $request->reason;
            $certification->revoked_at = now();
            $certification->revoked_by = auth()->id();
            $certification->save();

            // TODO: Send notification to individual about revocation

            DB::commit();

            return redirect()->route('admin.diving_professional_certifications.show', $certification)
                ->with('success', __('diving.certification_revoked_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', __('diving.failed_to_revoke_certification'));
        }
    }

    public function update(Request $request, DivingProfessionalCertification $certification): RedirectResponse
    {
        $validated = $request->validate([
            'certification_name' => 'required|string|max:255',
            'certification_number' => 'required|string|max:255',
            'certification_system' => 'required|in:'.implode(',', config('diving.certification_systems')),
            'certification_level' => 'required|in:diver_level_3,instructor_level_1,instructor_level_2,instructor_level_3,first_aid_bls_oxygen,compressor_operator',
            'issue_date' => 'required|date',
            'expiration_date' => 'nullable|date|after:issue_date',
        ]);

        try {
            DB::beginTransaction();

            // Log the changes
            $changes = [];
            foreach ($validated as $key => $value) {
                if ($value != $certification->$key) {
                    $changes[$key] = [
                        'old' => $certification->$key,
                        'new' => $value,
                    ];
                }
            }

            // Update the certification
            $certification->fill($validated);
            $certification->save();

            // Log activity
            if (! empty($changes)) {
                activity()
                    ->performedOn($certification)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'changes' => $changes,
                        'certification_id' => $certification->id,
                        'individual_name' => $certification->individual->full_name,
                    ])
                    ->log('Updated diving professional certification details');
            }

            DB::commit();

            return redirect()->route('admin.diving_professional_certifications.show', $certification)
                ->with('success', __('diving.certification_updated_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update certification: ' . $e->getMessage());

            return back()->with('error', __('diving.failed_to_update_certification'));
        }
    }

    public function destroy(Request $request, DivingProfessionalCertification $certification): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Store certification data for logging before deletion
            $certificationData = [
                'id' => $certification->id,
                'certification_name' => $certification->certification_name,
                'certification_system' => $certification->certification_system,
                'certification_level' => $certification->certification_level,
                'certification_number' => $certification->certification_number,
                'individual_name' => $certification->individual->full_name,
                'individual_code' => $certification->individual->member_code,
                'status' => $certification->state->name(),
                'deleted_at' => now(),
                'deleted_by' => Auth::user()->name,
            ];

            // Log the activity before deletion
            $logDescription = sprintf(
                'Deleted %s %s certification for %s (%s)',
                $certification->certification_system,
                $certification->certification_name,
                $certification->individual->full_name,
                $certification->individual->member_code
            );

            activity('DivingCertificationDeleted')
                ->performedOn($certification)
                ->causedBy(Auth::user())
                ->withProperties($certificationData)
                ->log($logDescription);

            // Delete the certification
            $certification->delete();

            DB::commit();

            return redirect()->route('admin.diving_professional_certifications.index')
                ->with('success', __('diving.certification_deleted_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete diving professional certification: ' . $e->getMessage());

            return back()->with('error', __('diving.failed_to_delete_certification'));
        }
    }

    public function downloadDocument(DivingProfessionalCertification $certification): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $media = $certification->getFirstMedia('certificate_documents');

        if (! $media) {
            return back()->with('error', __('diving.no_document_uploaded'));
        }

        try {
            if (! $this->mediaFileExists($media)) {
                Log::error('Diving certification document file not found', [
                    'certification_id' => $certification->id,
                    'media_id' => $media->id,
                ]);

                return back()->with('error', __('diving.document_file_not_found'));
            }

            return $this->streamMediaDownload($media, $media->file_name);
        } catch (\Exception $e) {
            Log::error('Error downloading diving certification document', [
                'certification_id' => $certification->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('diving.document_download_error'));
        }
    }
}
