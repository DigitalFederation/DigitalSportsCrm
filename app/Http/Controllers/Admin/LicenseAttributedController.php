<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Common\BaseLicenseAttributedController;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Actions\ActivateLicenseAttributedAction;
use Domain\Licenses\Actions\ApproveLicenseAttributedAction;
use Domain\Licenses\Actions\DeleteLicenseAttributedAction;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\PendingToProvisionalTransition;
use Domain\Licenses\States\WaitingApprovalLicenseAttributedState;
use Domain\Licenses\Transitions\PendingValidationToPendingTransition;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $query = QueryBuilder::for(LicenseAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee'),
                AllowedFilter::scope('filter_holder_type', 'holder_type'),
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_federation', 'federation'),
                AllowedFilter::scope('filter_entity', 'entity'),
                AllowedFilter::scope('filter_country', 'country'),
                AllowedFilter::scope('filter_member_code', 'member_code'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_category', 'professionalRole'),
                AllowedFilter::scope('filter_name', 'license_name'),
                AllowedFilter::scope('filter_status', 'license_attributed_status'),
                AllowedFilter::scope('filter_zone'),
            ])
            ->with('federation.country', 'owner');

        // Handle committee filter
        $committeeFilter = request()->input('filter')['committee'] ?? null;
        if (! empty($committeeFilter)) {
            // For diving committee, show only international licenses
            if (strtolower($committeeFilter) === 'diving') {
                $query->withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
                    ->whereHas('license', function ($q) {
                        $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
                    });
            } else {
                // For other committees, show all licenses (existing behavior)
                $query->withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class);
            }
        }

        $licenses = $query->latest()
            ->paginate()
            ->appends(request()->query());

        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();
        $cmas_zones = GeoZone::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Canceled')],
            'provisional' => ['id' => 'provisional', 'name' => __('Provisional')],
            'suspended' => ['id' => 'suspended', 'name' => __('Suspended')],
            'waiting_approval' => ['id' => 'waiting_approval', 'name' => __('Waiting Approval')],
            'expired' => ['id' => 'expired', 'name' => __('Expired')],
        ];

        $title = '';
        if (! empty(request()->input('filter')['committee'])) {
            $title = ucwords(request()->input('filter')['committee']);

            if (! empty(request()->input('filter')['filter_holder_type'])) {
                $title .= ' ' . ucwords(request()->input('filter')['filter_holder_type']);
            } else {
                $title .= ' ' . __(' Licenses');
            }
        } else {
            if (! empty(request()->input('filter')['filter_holder_type'])) {
                $title = ucwords(request()->input('filter')['filter_holder_type']) . ' ' . __('Licenses');
            } else {
                $title = __('All Licenses');
            }
        }

        return view('web.admin.license_attributed.index', compact('licenses', 'federations', 'countries', 'sports', 'filter_status', 'title', 'professional_roles', 'cmas_zones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $license_type_name, string $committee, Request $request): View
    {

        if ($license_type_name === 'entity') {
            $entities = Entity::select('id', 'name')->orderBy('name')->get();
        }

        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $professional_role = $request->professional_role ? strtoupper($request->professional_role) : null;

        return view(
            'web.admin.license_attributed.create',
            [
                'professional_role' => $professional_role,
                'license_type_name' => $license_type_name,
                'committee' => $committee,
                'federations' => $federations,
                'federation' => null,
                'requester_model_type' => 'federation',
                'entities' => $entities ?? null,
            ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {
        $license = LicenseAttributed::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
            ->with('owner')
            ->where(compact('id'))
            ->firstOrFail();

        $license_by_country = LicenseAttributed::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
            ->with('license', 'owner', 'federation.country')
            ->where('license_id', $license->license_id)
            ->whereHas('owner', function (Builder $query) use ($license) {
                $query->where('id', $license->model_id);
            })->get();

        return view(
            'web.admin.license_attributed.show',
            compact(
                'license',
                'license_by_country'
            )
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        string $id,
        DeleteLicenseAttributedAction $deleteLicenseAttributed
    ): RedirectResponse {
        try {

            DB::beginTransaction();

            $license = LicenseAttributed::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
                ->findOrFail($id);
            // Remove any constraints that might prevent deletion
            $license->delete();

            // Log the activity
            // Create a concise but informative log description
            $logDescription = sprintf(
                'Deleted %s license for %s. Holder: %s',
                $license->license_name,
                $license->owner_type === \Domain\Individuals\Models\Individual::class ? 'individual' : 'entity',
                $license->holder_name
            );
            activity()
                ->performedOn($license)
                ->causedBy(Auth::user())
                ->withProperties([
                    'license_id' => $license->id,
                    'license_name' => $license->license_name,
                    'holder_name' => $license->holder_name,
                    'holder_type' => $license->owner_type,
                    'federation_id' => $license->federation_id,
                ])
                ->log($logDescription);

            DB::commit();

            return redirect()->back()->with('success', 'License removed with success.');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return redirect()->back()->with('error', $ex->getMessage());
        }
    }

    public function activate(string $id, Request $request, ActivateLicenseAttributedAction $activate): RedirectResponse
    {
        try {
            db::beginTransaction();
            $license = LicenseAttributed::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
                ->find($id);
            if (! empty($license)) {
                $activate($license, $request->current_term_ends_at, bypassPaymentCheck: true);
            } else {
                throw new Exception(__('License not found'));
            }
        } catch (Exception $e) {
            db::rollBack();
            Log::error($e->getCode() . ': ' . $e->getMessage());

            return redirect()->back()->with('error', __('Error activating license.'));
        }

        db::commit();

        return redirect()->back()->with('success', __('License activated with success.'));
    }

    public function provisional(Request $request, PendingToProvisionalTransition $toProvisional): RedirectResponse
    {
        try {
            db::beginTransaction();
            $license = LicenseAttributed::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
                ->with('license')
                ->find($request->license_id);

            if (! empty($license) && $license->status_class == PendingLicenseAttributedState::class) {
                $toProvisional($license);
            } else {
                throw new Exception(__('License not found or not in pending state'));
            }
        } catch (Exception $e) {
            db::rollBack();
            Log::error($e->getCode() . ': ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error approving license:' . ' ' . $e->getMessage());
        }

        db::commit();

        return redirect()->back()->with('success', 'License request approved with success');
    }

    /**
     * Approves a LicenseAttributed entity that is currently in the 'Waiting Approval' state.
     *
     * This method handles the transition of a LicenseAttributed entity from the 'Waiting Approval' state to the 'Pending' state.
     * It is intended to be used by administrators to approve license requests that require pre-approval.
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
            $licenseAttributed = LicenseAttributed::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
                ->findOrFail($id);

            // Check if the license is in the WaitingApproval or PendingValidation state
            if ($licenseAttributed->status_class === WaitingApprovalLicenseAttributedState::class) {
                $approve($licenseAttributed);
            } elseif ($licenseAttributed->status_class === \Domain\Licenses\States\PendingValidationLicenseAttributedState::class) {
                // For PendingValidation state, we need to check if it requires payment
                if ($licenseAttributed->total_value > 0) {
                    // Transition to pending payment and create payment document
                    $transition = new PendingValidationToPendingTransition($licenseAttributed);
                    $transition->handle();
                } else {
                    // No payment required, activate directly
                    $activateAction = app(\Domain\Licenses\Actions\ActivateLicenseAttributedAction::class);
                    $activateAction($licenseAttributed, null);
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
                    ->log('License validated by admin');
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
                $license = LicenseAttributed::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
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
                    ->log($action === 'validation_rejected' ? 'License validation rejected by admin' : 'License canceled by admin');

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
