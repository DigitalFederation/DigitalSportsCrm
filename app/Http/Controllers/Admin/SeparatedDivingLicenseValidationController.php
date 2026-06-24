<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Domain\Licenses\Transitions\PendingValidationToActiveTransition;
use Domain\Licenses\Transitions\PendingValidationToCanceledTransition;
use Domain\Licenses\Transitions\PendingValidationToPendingTransition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeparatedDivingLicenseValidationController extends Controller
{
    /**
     * Get the morph alias for the given holder type.
     * Returns the morph alias ('entity' or 'individual') as stored in the database.
     */
    private function getMorphAlias(string $holderType): string
    {
        return $holderType; // 'entity' or 'individual' - matches the morph map in AppServiceProvider
    }
    // ===================
    // ENTITY ROUTES
    // ===================

    public function entityIndex(Request $request): View
    {
        return $this->renderValidationPage($request, 'entity');
    }

    public function entityShow(LicenseAttributed $licenseAttributed): View
    {
        return $this->renderShowPage($licenseAttributed, 'entity');
    }

    public function entityApprove(Request $request, LicenseAttributed $licenseAttributed): RedirectResponse
    {
        return $this->handleApprove($request, $licenseAttributed, 'entity');
    }

    public function entityReject(Request $request, LicenseAttributed $licenseAttributed): RedirectResponse
    {
        return $this->handleReject($request, $licenseAttributed, 'entity');
    }

    public function entityDestroy(LicenseAttributed $licenseAttributed): RedirectResponse
    {
        return $this->handleDestroy($licenseAttributed, 'entity');
    }

    // ===================
    // INDIVIDUAL ROUTES
    // ===================

    public function individualIndex(Request $request): View
    {
        return $this->renderValidationPage($request, 'individual');
    }

    public function individualShow(LicenseAttributed $licenseAttributed): View
    {
        return $this->renderShowPage($licenseAttributed, 'individual');
    }

    public function individualApprove(Request $request, LicenseAttributed $licenseAttributed): RedirectResponse
    {
        return $this->handleApprove($request, $licenseAttributed, 'individual');
    }

    public function individualReject(Request $request, LicenseAttributed $licenseAttributed): RedirectResponse
    {
        return $this->handleReject($request, $licenseAttributed, 'individual');
    }

    public function individualDestroy(LicenseAttributed $licenseAttributed): RedirectResponse
    {
        return $this->handleDestroy($licenseAttributed, 'individual');
    }

    // ===================
    // PRIVATE HELPERS
    // ===================

    private function renderValidationPage(Request $request, string $holderType): View
    {
        $morphAlias = $this->getMorphAlias($holderType);

        // Status filter options
        $statusMap = [
            'pending_td' => PendingTechnicalDirectorApprovalLicenseAttributedState::class,
            'pending_validation' => PendingValidationLicenseAttributedState::class,
            'approved' => ActiveLicenseAttributedState::class,
            'rejected' => CanceledLicenseAttributedState::class,
        ];

        $statusOptions = collect([
            'pending_td' => __('diving.filter_pending_td'),
            'pending_validation' => __('diving.filter_pending_validation'),
            'approved' => __('diving.filter_approved'),
            'rejected' => __('diving.filter_rejected'),
        ]);

        if ($holderType === 'individual') {
            $statusOptions->forget('pending_td');
        }

        $committeeCodes = $holderType === 'individual' ? ['DIVINGSERVICES'] : ['DIVING', 'DIVINGSERVICES'];

        // Dropdown data
        $entities = Entity::orderBy('name')->pluck('name', 'id');
        $divingLicenses = License::withoutGlobalScopes()
            ->whereHas('committee', fn ($q) => $q->whereIn('code', $committeeCodes))
            ->orderBy('name')
            ->pluck('name', 'id');

        // Read filter values
        $filterName = $request->input('filter.name');
        $filterEntity = $request->input('filter.entity');
        $filterLicense = $request->input('filter.license');
        $filterStatus = $request->input('filter.status');
        $filterMemberNumber = $request->input('filter.member_number');
        $search = $request->input('search');
        $statusClass = $statusMap[$filterStatus] ?? null;

        $eagerLoads = ['owner', 'license.committee'];
        if ($holderType === 'entity') {
            $eagerLoads[] = 'divingTechnicalDirectorInvitations.individual';
            $eagerLoads[] = 'divingTechnicalDirectors';
        }

        $licenses = LicenseAttributed::with($eagerLoads)
            ->where('model_type', $morphAlias)
            ->when($statusClass, fn ($query, $class) => $query->where('status_class', $class))
            ->whereHas('license.committee', fn ($query) => $query->whereIn('code', $committeeCodes))
            ->when($search, function ($query, $term) use ($morphAlias) {
                $query->where(function ($query) use ($term, $morphAlias) {
                    $query->where('holder_name', 'like', "%{$term}%")
                        ->orWhere('license_name', 'like', "%{$term}%")
                        ->orWhere('license_number', 'like', "%{$term}%")
                        ->orWhereHas('owner', function ($q) use ($term, $morphAlias) {
                            if ($morphAlias === 'individual') {
                                $q->where('name', 'like', "%{$term}%")
                                    ->orWhere('surname', 'like', "%{$term}%");
                            } else {
                                $q->where('name', 'like', "%{$term}%");
                            }
                        });
                });
            })
            ->when($filterEntity, fn ($query, $entityId) => $query->where('model_id', $entityId))
            ->when($filterLicense, fn ($query, $licenseId) => $query->where('license_id', $licenseId))
            ->when($filterName, function ($query, $name) use ($morphAlias) {
                $query->where(function ($query) use ($name, $morphAlias) {
                    $query->where('holder_name', 'like', "%{$name}%")
                        ->orWhereHas('owner', function ($q) use ($name, $morphAlias) {
                            if ($morphAlias === 'individual') {
                                $q->where('name', 'like', "%{$name}%")
                                    ->orWhere('surname', 'like', "%{$name}%");
                            } else {
                                $q->where('name', 'like', "%{$name}%");
                            }
                        });
                });
            })
            ->when($filterMemberNumber, function ($query, $memberNumber) {
                $query->whereHasMorph('owner', [Entity::class], function ($q) use ($memberNumber) {
                    $q->where('member_number', 'like', "%{$memberNumber}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $pageTitle = $holderType === 'entity'
            ? __('diving.diving_service_providers_title')
            : __('diving.individual_license_validation_title');

        $pageSubtitle = $holderType === 'entity'
            ? __('diving.diving_service_providers_subtitle')
            : __('diving.individual_license_validation_subtitle');

        return view('web.admin.diving_license_validation.index', compact(
            'licenses',
            'holderType',
            'pageTitle',
            'pageSubtitle',
            'entities',
            'divingLicenses',
            'statusOptions'
        ));
    }

    private function renderShowPage(LicenseAttributed $licenseAttributed, string $holderType): View
    {
        // Validate holder type matches
        $morphAlias = $this->getMorphAlias($holderType);
        if ($licenseAttributed->model_type !== $morphAlias) {
            abort(404);
        }

        // Ensure it's a diving license (DIVING or DIVINGSERVICES committee)
        if (! in_array($licenseAttributed->license->committee->code, ['DIVING', 'DIVINGSERVICES'])) {
            abort(404);
        }

        $eagerLoads = [
            'owner',
            'license',
            'divingTechnicalDirectorInvitations.individual',
            'divingTechnicalDirectorInvitations.individual.divingProfessionalCertifications',
            'divingTechnicalDirectorInvitations.individual.certificationsAttributed',
            'divingTechnicalDirectors',
        ];

        if ($holderType === 'individual') {
            $eagerLoads[] = 'owner.country';
        }

        $licenseAttributed->load($eagerLoads);

        return view('web.admin.diving_license_validation.show', compact(
            'licenseAttributed',
            'holderType'
        ));
    }

    private function handleApprove(Request $request, LicenseAttributed $licenseAttributed, string $holderType): RedirectResponse
    {
        // Validate holder type matches
        $morphAlias = $this->getMorphAlias($holderType);
        if ($licenseAttributed->model_type !== $morphAlias) {
            abort(404);
        }

        // Ensure it's pending validation
        if ($licenseAttributed->status_class !== PendingValidationLicenseAttributedState::class) {
            return back()->with('error', __('diving.license_not_pending_validation'));
        }

        // For entity diving licenses, ensure all technical directors have approved
        $isDivingLicense = in_array($licenseAttributed->license->committee->code, ['DIVING', 'DIVINGSERVICES']);
        if ($holderType === 'entity' && $isDivingLicense) {
            $technicalDirectors = $licenseAttributed->divingTechnicalDirectors()
                ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
                ->get();

            foreach ($technicalDirectors as $director) {
                if (! $director->hasApproved()) {
                    return back()->with('error', __('diving.all_technical_directors_must_approve_before_admin_validation'));
                }
            }
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Check if payment is required
            $requiresPayment = $licenseAttributed->total_value > 0;

            if ($requiresPayment) {
                // Transition to pending payment
                $transition = new PendingValidationToPendingTransition($licenseAttributed);
                $transition->handle();
                $message = __('diving.license_approved_pending_payment');
            } else {
                // Transition to active
                $transition = new PendingValidationToActiveTransition($licenseAttributed);
                $transition->handle();
                $message = __('diving.license_approved_and_activated');
            }

            // Store approval notes if provided
            if (! empty($validated['notes'])) {
                $licenseAttributed->validation_notes = $validated['notes'];
                $licenseAttributed->validated_by = auth()->user()->id;
                $licenseAttributed->validated_at = now();
                $licenseAttributed->save();
            }

            DB::commit();

            return redirect()->route("admin.{$holderType}_diving_license_validation.index")
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve diving license: ' . $e->getMessage());

            return back()->with('error', __('diving.failed_to_approve_license'));
        }
    }

    private function handleReject(Request $request, LicenseAttributed $licenseAttributed, string $holderType): RedirectResponse
    {
        // Validate holder type matches
        $morphAlias = $this->getMorphAlias($holderType);
        if ($licenseAttributed->model_type !== $morphAlias) {
            abort(404);
        }

        // Ensure it's pending validation
        if ($licenseAttributed->status_class !== PendingValidationLicenseAttributedState::class) {
            return back()->with('error', __('diving.license_not_pending_validation'));
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Transition to canceled (this also stores validation_notes)
            $transition = new PendingValidationToCanceledTransition($licenseAttributed, $validated['reason']);
            $transition->handle();

            // Store who validated and when
            $licenseAttributed->validated_by = auth()->user()->id;
            $licenseAttributed->validated_at = now();
            $licenseAttributed->save();

            DB::commit();

            return redirect()->route("admin.{$holderType}_diving_license_validation.index")
                ->with('success', __('diving.license_rejected_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject diving license: ' . $e->getMessage());

            return back()->with('error', __('diving.failed_to_reject_license'));
        }
    }

    private function handleDestroy(LicenseAttributed $licenseAttributed, string $holderType): RedirectResponse
    {
        // Validate holder type matches
        $morphAlias = $this->getMorphAlias($holderType);
        if ($licenseAttributed->model_type !== $morphAlias) {
            abort(404);
        }

        // Ensure it's a diving license (DIVING or DIVINGSERVICES committee)
        if (! in_array($licenseAttributed->license->committee->code, ['DIVING', 'DIVINGSERVICES'])) {
            abort(404);
        }

        try {
            DB::beginTransaction();

            // Store license info for the success message
            $licenseName = $licenseAttributed->license_name;
            $holderName = $licenseAttributed->holder_name;

            // Delete any related documents
            $licenseAttributed->officialDocuments()->delete();

            // Delete any related technical director invitations
            $licenseAttributed->divingTechnicalDirectorInvitations()->delete();

            // Delete any related technical directors
            $licenseAttributed->divingTechnicalDirectors()->delete();

            // Delete the license
            $licenseAttributed->delete();

            DB::commit();

            return redirect()->route("admin.{$holderType}_diving_license_validation.index")
                ->with('success', __('diving.license_deleted_successfully', [
                    'license' => $licenseName,
                    'holder' => $holderName,
                ]));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete diving license: ' . $e->getMessage());

            return back()->with('error', __('diving.failed_to_delete_license'));
        }
    }
}
