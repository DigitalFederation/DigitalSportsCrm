<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Notifications\CreateMembershipNotification;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Actions\AssignLocalMembershipPlanAction;
use Domain\Memberships\Models\LocalMembershipPlan;
use Domain\Memberships\Models\MembershipPlan;
use Domain\Memberships\States\ActiveMembershipState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocalMembershipPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('ensureIsMainFederation'); // Custom middleware to ensure only main federation has access
    }

    public function index(): View
    {
        // Fetch local federations that belong to the logged-in main federation
        $localFederations = Federation::with(['localMembershipPlan.membershipPlan'])
            ->where('parent_id', auth()->user()->federations()->value('federation.id'))
            ->filterIsLocal(true)
            ->select('id', 'name')
            ->get();

        return view('web.federation.local_membership_plan_association.index', compact('localFederations'));
    }

    public function create(): View
    {
        $loggedInFederation = auth()->user()->federations()->value('federation.id');

        // Fetch local federations that belong to the logged-in main federation
        $localFederations = Federation::where('parent_id', $loggedInFederation)
            ->filterIsLocal(true)
            ->select('id', 'name')
            ->get();

        // Fetch active membership plans that belong to the logged-in main federation
        $membershipPlans = MembershipPlan::whereHas('memberships', function ($query) use ($loggedInFederation) {
            $query->where('federation_id', $loggedInFederation)->where('status_class', ActiveMembershipState::class);
        })->get(['id', 'name']);

        return view('web.federation.local_membership_plan_association.create', compact('localFederations', 'membershipPlans'));
    }

    public function edit($id): View
    {

        // Fetch the specific local federation by its ID along with its associated memberships
        $localFederation = Federation::with(['localMembershipPlan.membershipPlan'])
            ->where('id', $id)
            ->where('parent_id', auth()->user()->federations()->value('federation.id'))
            ->firstOrFail();

        // Fetch available and active memberships for the logged-in main federation
        $membershipPlans = MembershipPlan::whereHas('memberships', function ($query) {
            $query->where('federation_id', auth()->user()->federations()->value('federation.id'))
                ->where('status_class', ActiveMembershipState::class);
        })->get();

        // Fetch activity logs related to the local federation's membership plan changes
        $activityLogs = \Spatie\Activitylog\Models\Activity::where('subject_type', Federation::class)
            ->where('subject_id', $id)
            ->latest()
            ->get();

        return view('web.federation.local_membership_plan_association.edit', compact('localFederation', 'membershipPlans', 'activityLogs'));
    }

    public function store(
        Request $request,
        AssignLocalMembershipPlanAction $action
    ) {
        $validated = $request->validate([
            'local_federation_id' => 'required|integer',
            'membership_plan_id' => 'required|array',
            'membership_plan_id.*' => 'integer|exists:membership_plan,id',
        ]);

        $action->execute($validated['local_federation_id'], $validated['membership_plan_id']);

        foreach (Federation::findOrFail($validated['local_federation_id'])->users as $user) {
            $user->notify(new CreateMembershipNotification);
        }

        return redirect()->route('federation.local-membership-plan.index')->with('success', 'Membership plan assigned successfully');
    }

    public function update(
        Request $request,
        int $localFederationId,
        AssignLocalMembershipPlanAction $action
    ) {
        // Validate the request data
        $validated = $request->validate([
            'membership_plan_id' => ['sometimes', 'array'],
            'membership_plan_id.*' => 'integer|exists:membership_plan,id',
        ]);

        try {
            // Fetch the local federation
            $localFederation = Federation::findOrFail($localFederationId);

            // Ensure it's a local federation
            if (! $localFederation->is_local) {
                return redirect()->back()->withErrors(['error' => 'Invalid local federation']);
            }

            // Sync the memberships
            // Ensure an empty array is passed if 'membership_plan_id' is not set
            $membershipPlanIds = $validated['membership_plan_id'] ?? [];
            $action->execute($localFederationId, $membershipPlanIds);

            // Redirect back with a success message
            return redirect()->route('federation.local-membership-plan.index')
                ->with('success', 'Membership plans updated successfully for '.$localFederation->name);
        } catch (Exception $e) {
            // Log the error
            Log::error($e->getMessage());

            // Redirect back with an error message
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating membership plans']);
        }
    }

    public function destroy($id): RedirectResponse
    {
        $association = LocalMembershipPlan::findOrFail($id);
        $association->delete();

        return redirect()->route('localMembershipAssociation.index')->with('success', 'Membership association deleted successfully.');
    }
}
