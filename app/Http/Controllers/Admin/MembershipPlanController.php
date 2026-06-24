<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipPlanRequest;
use App\Models\Committee;
use App\Models\Sport;
use Domain\Licenses\Models\License;
use Domain\Memberships\Actions\CreateMembershipPlanAction;
use Domain\Memberships\Actions\DeleteMembershipPlanAction;
use Domain\Memberships\Actions\EditMembershipPlanAction;
use Domain\Memberships\DataTransferObject\MembershipPlanData;
use Domain\Memberships\Models\MembershipPlan;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $plans = QueryBuilder::for(MembershipPlan::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_committee'),
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_sport'),
                AllowedFilter::scope('filter_price'),
            ])
            ->with('committee')
            ->orderBy('created_at', 'DESC')
            ->paginate()
            ->appends(request()->query());

        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();

        // Manually insert the "international" option
        $committees->prepend(['id' => null, 'name' => config('branding.international.short_name', 'IF')]);

        return view('web.admin.membership_plan.index', compact('plans', 'committees', 'sports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $interval_unit = config('enum.interval_unit');
        $licenses = License::select('id', 'name')->orderBy('name')->pluck('name', 'id');

        return view('web.admin.membership_plan.create', compact('committees', 'interval_unit', 'licenses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MembershipPlanRequest $request, CreateMembershipPlanAction $createMembershipPlanAction): RedirectResponse
    {
        try {

            $validatedData = $request->validated();
            if ($validatedData['committee_id'] == 0) {
                $validatedData['committee_id'] = null; // Convert '0' to null
            }

            $plan = $createMembershipPlanAction(MembershipPlanData::fromArray($request->validated()));

        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return back()->with('error', 'Error creating a membership plan. '.$ex->getMessage());
        }

        return redirect(route('admin.membership-plan.index'))->with('success', 'Membership plan created with success.');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $plan = MembershipPlan::where(compact('id'))->with('committee', 'licenses.type', 'licenses.committee')->firstOrFail();

        return view('web.admin.membership_plan.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $plan = MembershipPlan::with('licenses')->find($id);
        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $interval_unit = config('enum.interval_unit');
        $licenses = License::select('id', 'name')->orderBy('name')->pluck('name', 'id');

        return view('web.admin.membership_plan.edit', compact('plan', 'committees', 'interval_unit', 'licenses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MembershipPlanRequest $request, int $id, EditMembershipPlanAction $editMembershipPlanAction): RedirectResponse
    {
        try {
            $edit = $editMembershipPlanAction(MembershipPlanData::fromArray($request->validated()), $id);

        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return back()->with('error', 'Error updating the membership plan. '.$ex->getMessage());
        }

        return redirect(route('admin.membership-plan.index'))->with('success', 'Membership plan updated with success.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id, DeleteMembershipPlanAction $deleteMembershipPlanAction): RedirectResponse
    {
        try {
            $deleted = $deleteMembershipPlanAction($id);

        } catch (Exception $ex) {
            if ($ex->getCode() == 802) {
                return back()->with('error', $ex->getMessage());
            }

            Log::error($ex->getMessage());

            return back()->with('error', __('Error creating this record, please contact the administrator.'));
        }

        return back()->with('success', __('Membership plan deleted.'));
    }
}
