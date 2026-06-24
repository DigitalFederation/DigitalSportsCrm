<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InsuranceUpdateRequest;
use App\QueryFilters\FilterMemberNameByWordStart;
use Domain\Insurance\Actions\UpdateInsuranceAction;
use Domain\Insurance\DataTransferObject\InsuranceData;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InsuranceController extends Controller
{
    public function index(Request $request): View
    {
        $query = QueryBuilder::for(Insurance::class)
            ->allowedFilters([
                AllowedFilter::exact('member_type'),
                AllowedFilter::exact('insurance_plan_id'),
                AllowedFilter::exact('status_class'),
                AllowedFilter::custom('member.name', new FilterMemberNameByWordStart),
                'member.member_number',
                'requester.name',
            ])
            ->with(['member', 'insurancePlan', 'memberSubscription', 'requester']);

        // Filter by activation date range (created_at)
        if ($request->filled('filter_activation_start')) {
            $query->whereDate('created_at', '>=', $request->filter_activation_start);
        }

        if ($request->filled('filter_activation_end')) {
            $query->whereDate('created_at', '<=', $request->filter_activation_end);
        }

        $insurances = $query->orderBy('created_at', 'desc')
            ->paginate()
            ->appends(request()->query());

        $insurancePlans = InsurancePlan::orderBy('name')->pluck('name', 'id');

        return view('web.admin.insurances.index', compact('insurances', 'insurancePlans'));
    }

    public function show(Insurance $insurance): View
    {
        // Eager load relationships
        $insurance->load([
            'member.country',
            'member.district',
            'member.entities',
            'member.federations',
            'insurancePlan.media',
            'memberSubscription.membershipPackage',
            'requester',
        ]);

        $individual = $insurance->member;

        // Defensive: Only allow if member is Individual
        if (! $individual instanceof \Domain\Individuals\Models\Individual) {
            abort(404, __('insurances.individual_members_only'));
        }

        $startDateFormatted = $insurance->start_date->format('d/m/Y');
        $endDateFormatted = $insurance->end_date->format('d/m/Y');

        return view('web.admin.insurances.show', [
            'insurance' => $insurance,
            'individual' => $individual,
            'startDateFormatted' => $startDateFormatted,
            'endDateFormatted' => $endDateFormatted,
        ]);
    }

    public function edit(Insurance $insurance): View
    {
        $insurance->load(['member', 'insurancePlan', 'memberSubscription']);

        return view('web.admin.insurances.edit', compact('insurance'));
    }

    public function update(
        InsuranceUpdateRequest $request,
        Insurance $insurance,
        UpdateInsuranceAction $action
    ): RedirectResponse {
        $validatedData = $request->validated();
        $validatedData['member_type'] = $insurance->member_type;

        $data = InsuranceData::fromArray($validatedData);

        $updatedInsurance = $action($insurance, $data);

        return redirect()->route('admin.insurances.show', $updatedInsurance)
            ->with('success', __('insurances.updated_successfully'));
    }

    public function destroy(Insurance $insurance): RedirectResponse
    {
        $insurance->load(['insurancePlan', 'member']);

        try {
            // Log activity before deletion
            activity()
                ->performedOn($insurance)
                ->withProperties([
                    'insurance_plan_id' => $insurance->insurance_plan_id,
                    'insurance_plan_name' => $insurance->insurancePlan->name ?? __('common.not_available'),
                    'member_type' => $insurance->member_type,
                    'member_id' => $insurance->member_id,
                    'member_name' => $insurance->member ? $insurance->member->name : __('common.not_available'),
                    'start_date' => $insurance->start_date->format('Y-m-d'),
                    'end_date' => $insurance->end_date->format('Y-m-d'),
                    'policy_number' => $insurance->policy_number,
                    'individual_fee' => $insurance->individual_fee,
                    'entity_fee' => $insurance->entity_fee,
                    'is_external' => $insurance->is_external,
                ])
                ->log('Insurance deleted');

            $insurance->delete();

            return redirect()->route('admin.insurances.index')
                ->with('success', __('insurances.deleted_successfully'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('insurances.delete_failed'));
        }
    }

    public function updateStatus(Request $request, Insurance $insurance): RedirectResponse
    {
        $insurance->load(['insurancePlan', 'member']);

        $request->validate([
            'status_class' => 'required|string|in:' . implode(',', [
                \Domain\Insurance\States\ActiveInsuranceState::class,
                \Domain\Insurance\States\PendingPaymentInsuranceState::class,
                \Domain\Insurance\States\InactiveInsuranceState::class,
                \Domain\Insurance\States\SuspendedInsuranceState::class,
                \Domain\Insurance\States\ExpiredInsuranceState::class,
            ]),
        ]);

        try {
            $oldStatusClass = $insurance->status_class;
            $newStatusClass = $request->status_class;

            // Update the status_class
            $insurance->update(['status_class' => $newStatusClass]);

            // Log the status change
            activity()
                ->performedOn($insurance)
                ->withProperties([
                    'old_status_class' => $oldStatusClass,
                    'new_status_class' => $newStatusClass,
                    'insurance_plan_name' => $insurance->insurancePlan->name ?? __('common.not_available'),
                    'member_type' => $insurance->member_type,
                    'member_id' => $insurance->member_id,
                    'member_name' => $insurance->member ? $insurance->member->name : __('common.not_available'),
                    'policy_number' => $insurance->policy_number,
                ])
                ->log('Insurance status changed');

            return redirect()->back()
                ->with('success', __('insurances.status_updated_successfully'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('insurances.status_update_failed'));
        }
    }
}
