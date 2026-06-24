<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Common\BaseLicenseAttributedController;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Actions\ActivateLicenseAttributedAction;
use Domain\Licenses\Actions\ApproveLicenseAttributedAction;
use Domain\Licenses\Actions\DeleteLicenseAttributedAction;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\PendingToProvisionalTransition;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Domain\Licenses\States\WaitingApprovalLicenseAttributedState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LicenseAttributedController extends BaseLicenseAttributedController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Retrieve the current federation
        $currentFederation = auth()->user()->federations()->first();
        $currentFederationId = $currentFederation->id;

        // Get committees this federation can manage (non-international only for federation namespace)
        $allowedCommitteeIds = $currentFederation->committees()
            ->where('committee.is_international', false)
            ->pluck('committee.id')
            ->toArray();

        $query = QueryBuilder::for(LicenseAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee'),
                AllowedFilter::scope('filter_holder_type', 'holder_type'),
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_entity', 'entity'),
                AllowedFilter::scope('filter_member_code', 'member_code'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_category', 'professionalRole'),
                AllowedFilter::scope('filter_name', 'license_name'),
                AllowedFilter::scope('filter_status', 'license_attributed_status'),
                AllowedFilter::scope('filter_professional'),
            ])
            ->with('owner');

        // Main federation sees all licenses.
        if ($currentFederation->is_default_federation) {
            // No federation filter - show all licenses
            // Filter by committees the federation can manage (non-international)
            $query->whereHas('license', function ($q) use ($allowedCommitteeIds) {
                $q->whereIn('committee_id', $allowedCommitteeIds);
            });
        } elseif ($currentFederation->is_local) {
            // Local/Territorial federations see only licenses of their members
            // Get entity IDs that belong to this local federation
            $entityIds = DB::table('entity_federation')
                ->where('federation_id', $currentFederationId)
                ->pluck('entity_id');

            // Get individual IDs that belong to this local federation
            $individualIds = DB::table('individual_federation')
                ->where('federation_id', $currentFederationId)
                ->pluck('individual_id');

            $query->where(function ($q) use ($entityIds, $individualIds) {
                $q->where(function ($subQ) use ($entityIds) {
                    $subQ->where('model_type', 'entity')
                        ->whereIn('model_id', $entityIds);
                })->orWhere(function ($subQ) use ($individualIds) {
                    $subQ->where('model_type', 'individual')
                        ->whereIn('model_id', $individualIds);
                });
            });

            // Filter by committees the federation can manage (non-international)
            $query->whereHas('license', function ($q) use ($allowedCommitteeIds) {
                $q->whereIn('committee_id', $allowedCommitteeIds);
            });
        } else {
            // Other federations (modalidade) - filter by federation_id
            $query->where('federation_id', $currentFederationId)
                ->whereHas('license', function ($q) use ($allowedCommitteeIds) {
                    $q->whereIn('committee_id', $allowedCommitteeIds);
                });
        }

        $licenses = $query
            ->allowedSorts('name', 'license_name', 'activated_at')
            ->defaultSort('-created_at')
            ->paginate()
            ->appends(request()->query());

        $sports = Sport::select('id', 'name')->orderBy('name')->get();

        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Suspended')],
        ];

        // If no Holder Type is selected, the default is Individual
        if (empty(request()->input('filter')['filter_holder_type'])) {
            request()->merge(['filter_holder_type' => 'individual']);
        }

        $professional = '';
        if (! empty(request()->input('filter')['filter_professional'])) {
            if (request()->input('filter')['filter_professional'] == 'refereejudge') {
                $professional = 'Referee & Judge';
            } elseif (request()->input('filter')['filter_professional'] == 'instructorleader') {
                $professional = 'Instructor & Leader';
            } else {
                $professional = ucwords(request()->input('filter')['filter_professional']);
            }
        }

        if (! empty(request()->input('filter')['committee'])) {
            $title = ucwords(request()->input('filter')['committee']);

            if (! empty(request()->input('filter')['filter_holder_type'])) {
                if (! empty(request()->input('filter')['filter_professional'])) {
                    $title .= ' '.$professional.' '.__('Licenses');
                } else {
                    $title .= ' '.ucwords(request()->input('filter')['filter_holder_type']).' '.__('Licenses');
                }
            } else {
                $title .= ' '.__(' Licenses');
            }
        } else {
            if (! empty(request()->input('filter')['filter_holder_type'])) {
                $title = ucwords(request()->input('filter')['filter_holder_type']).' '.__('Licenses');
            } else {
                $title = __('All Licenses');
            }
        }

        return view('web.federation.license_attributed.index', compact('licenses', 'sports', 'filter_status', 'professional_roles', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $license_type_name, string $committee, Request $request): View
    {
        $federation = auth()->user()->federations()->first();
        $professional_role = $request->professional_role ? strtoupper($request->professional_role) : null;

        if ($license_type_name === 'entity') {
            $entities = Entity::select('id', 'name')
                ->whereHas('entityFederations', function (Builder $query) use ($federation) {
                    $query->where('federation_id', $federation->id)
                        ->where('status_class', ActiveEntityFederationState::class);
                })
                ->orderBy('name')
                ->get();
        }

        return view(
            'web.federation.license_attributed.create',
            [
                'professional_role' => $professional_role,
                'requester_model_type' => 'federation',
                'license_type_name' => $license_type_name,
                'committee' => $committee,
                'federation' => $federation,
                'entities' => $entities ?? null,
            ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {
        $license = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->with(['owner', 'license.committee'])
            ->find($id);

        if (empty($license)) {
            return redirect()->back()->with('error', __('Record does not exist'));
        }

        // Check if user's federation can view this license (committee authorization)
        $federation = auth()->user()->federations()->first();
        $isDefaultFederation = (bool) $federation->is_default_federation;

        // Main federation can see all licenses like admin; others are restricted.
        if (! $isDefaultFederation && $license->license?->committee) {
            $committee = $license->license->committee;
            if ($committee->isInternational() || ! $federation->canManageCommittee($committee)) {
                abort(403, __('federation.cannot_manage_committee'));
            }
        }

        return view('web.federation.license_attributed.show', compact('license', 'isDefaultFederation'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, DeleteLicenseAttributedAction $deleteLicenseAttributed): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $deleteLicenseAttributed($id);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return redirect()->back()->with('error', $ex->getMessage());
        }

        return redirect()->back()->with('success', __('licenses.license_deleted_successfully'));
    }

    /**
     * Only if the state is suspended, the license can be activated
     */
    public function activate(string $id, Request $request, ActivateLicenseAttributedAction $activate): RedirectResponse
    {
        try {
            db::beginTransaction();
            $license = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
                ->with('license')
                ->find($id);

            if (! empty($license) && ($license->status_class == SuspendedLicenseAttributedState::class || $license->status_class == PendingLicenseAttributedState::class)) {

                $activate($license, $request->current_term_ends_at);

            } else {
                throw new Exception(__('License not found or not in suspended state'));
            }
        } catch (Exception $e) {
            db::rollBack();
            Log::error($e->getCode().': '.$e->getMessage());

            return redirect()->back()->with('error', __('Error activating license.').' '.$e->getMessage());
        }

        db::commit();

        return redirect()->back()->with('success', __('License activated with success.'));
    }

    public function provisional(Request $request, PendingToProvisionalTransition $toProvisional): RedirectResponse
    {
        try {
            db::beginTransaction();
            $license = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
                ->with('license')
                ->find($request->license_id);

            if (! empty($license) && $license->status_class == PendingLicenseAttributedState::class) {
                $toProvisional($license);
            } else {
                throw new Exception(__('License not found or not in pending state'));
            }

        } catch (Exception $e) {
            db::rollBack();
            Log::error($e->getCode().': '.$e->getMessage());

            return redirect()->back()->with('error', 'Error approving license:'.' '.$e->getMessage());
        }

        db::commit();

        return redirect()->back()->with('success', 'License request approved with success');
    }

    /**
     * Approves a LicenseAttributed entity that is currently in the 'Waiting Approval' state.
     *
     * This method handles the transition of a LicenseAttributed entity from the 'Waiting Approval' state to the 'Pending' state.
     * It is intended to be used by Federation administrators to approve license requests that require pre-approval.
     * Upon approval, the license state is updated, and any additional processes (like notification or logging) are triggered.
     *
     * @param  string  $id  The unique identifier of the LicenseAttributed entity to be approved.
     * @param  ApproveLicenseAttributedAction  $approve  The action class responsible for handling the approval logic.
     * @return RedirectResponse Returns a redirect response, leading back to the previous page with a success or error message.
     *
     * @throws Exception If the license is not found, or if the license is not in the 'Waiting Approval' state.
     */
    public function approve(string $id, ApproveLicenseAttributedAction $approve): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $licenseAttributed = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
                ->findOrFail($id);

            // Check if the license is in the WaitingApproval or PendingValidation state
            if ($licenseAttributed->status_class === WaitingApprovalLicenseAttributedState::class) {
                $approve($licenseAttributed);
            } elseif ($licenseAttributed->status_class === \Domain\Licenses\States\PendingValidationLicenseAttributedState::class) {
                // For PendingValidation state, we need to check if it requires payment
                if ($licenseAttributed->total_value > 0) {
                    // Transition to pending payment
                    $licenseAttributed->status_class = PendingLicenseAttributedState::class;
                    $licenseAttributed->save();
                } else {
                    // No payment required, activate directly
                    $activateAction = app(\Domain\Licenses\Actions\ActivateLicenseAttributedAction::class);
                    $activateAction($licenseAttributed, null, true);
                }

                // Log the validation
                activity('license_validation')
                    ->causedBy(auth()->user())
                    ->performedOn($licenseAttributed)
                    ->withProperties([
                        'action' => 'validated',
                        'license_name' => $licenseAttributed->license_name,
                        'holder' => $licenseAttributed->holder_name,
                    ])
                    ->log('License validated by federation admin');
            } else {
                throw new Exception(__('licenses.license_not_in_approvable_state'));
            }

            DB::commit();

            return redirect()->back()->with('success', __('licenses.license_approved_successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', __('licenses.error_approving_license') . $e->getMessage());
        }
    }

    public function cancel(string $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $license = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
                    ->findOrFail($id);

                // Log the cancellation with appropriate context
                $action = $license->status_class === \Domain\Licenses\States\PendingValidationLicenseAttributedState::class
                    ? 'validation_rejected'
                    : 'canceled';

                activity('license_validation')
                    ->causedBy(auth()->user())
                    ->performedOn($license)
                    ->withProperties([
                        'action' => $action,
                        'license_name' => $license->license_name,
                        'holder' => $license->holder_name,
                        'previous_state' => class_basename($license->status_class),
                    ])
                    ->log($action === 'validation_rejected' ? 'License validation rejected by federation admin' : 'License canceled by federation admin');

                $license->status_class = CanceledLicenseAttributedState::class;
                $license->save();
            });

            return redirect()->back()->with('success', __('License request canceled successfully.'));
        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return redirect()->back()->with('error', $ex->getMessage());
        }
    }
}
