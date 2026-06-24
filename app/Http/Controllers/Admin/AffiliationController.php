<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\ExpiredAffiliationState;
use Domain\Memberships\States\InactiveAffiliationState;
use Domain\Memberships\States\PendingPaymentAffiliationState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Domain\Memberships\States\SuspendedAffiliationState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AffiliationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Affiliation::with(['member', 'federation', 'memberSubscription.membershipPackage', 'requester']);

        // Apply filters
        if ($request->filled('filter_member_type')) {
            $query->where('member_type', $request->filter_member_type);
        }

        if ($request->filled('filter_status_class')) {
            $query->where('status_class', $request->filter_status_class);
        }

        if ($request->filled('filter_member_name')) {
            $escapedName = addcslashes($request->filter_member_name, '%_');
            $query->whereHasMorph('member', ['*'], function ($q) use ($escapedName) {
                // Match names where search term is at start of name or start of a word
                $q->where('name', 'like', $escapedName . '%')
                    ->orWhere('name', 'like', '% ' . $escapedName . '%');
            });
        }

        if ($request->filled('filter_member_number')) {
            $escapedNumber = addcslashes($request->filter_member_number, '%_');
            $query->whereHasMorph('member', ['*'], function ($q) use ($escapedNumber) {
                $q->where('member_number', 'like', '%' . $escapedNumber . '%');
            });
        }

        if ($request->filled('filter_federation')) {
            $query->where('federation_id', $request->filter_federation);
        }

        // Filter by start_date range (both filters use start_date column)
        if ($request->filled('filter_start_date')) {
            $query->whereDate('start_date', '>=', $request->filter_start_date);
        }

        if ($request->filled('filter_end_date')) {
            $query->whereDate('start_date', '<=', $request->filter_end_date);
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $affiliations = $query->paginate(15)->withQueryString();

        // Get territorial associations (is_local = true) and main federation (is_default_federation = true) for filter
        $federations = Federation::where('is_local', true)
            ->orWhere('is_default_federation', true)
            ->get();

        return view('web.admin.affiliations.index', compact('affiliations', 'federations'));
    }

    public function updateStatus(Request $request, Affiliation $affiliation): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:active,pending_payment,inactive,suspended,expired',
        ]);

        try {
            DB::beginTransaction();

            // Update affiliation status based on the request
            switch ($request->status) {
                case 'active':
                    $affiliation->status_class = ActiveAffiliationState::class;
                    break;
                case 'pending_payment':
                    $affiliation->status_class = PendingPaymentAffiliationState::class;
                    break;
                case 'inactive':
                    $affiliation->status_class = InactiveAffiliationState::class;
                    break;
                case 'suspended':
                    $affiliation->status_class = SuspendedAffiliationState::class;
                    break;
                case 'expired':
                    $affiliation->status_class = ExpiredAffiliationState::class;
                    break;
            }

            $affiliation->save();

            // If setting to active and affiliation has a member subscription, also activate the subscription
            if ($request->status === 'active' && $affiliation->memberSubscription) {
                $subscription = $affiliation->memberSubscription;

                // Check if subscription is pending payment
                if ($subscription->status_class === PendingPaymentMemberSubscriptionState::class) {
                    $subscription->status_class = ActiveMemberSubscriptionState::class;
                    $subscription->save();

                    // Also activate related insurances if any
                    foreach ($subscription->insurances as $insurance) {
                        if ($insurance->status_class === \Domain\Insurance\States\InactiveInsuranceState::class) {
                            $insurance->status_class = \Domain\Insurance\States\ActiveInsuranceState::class;
                            $insurance->save();
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.affiliations.index')
                ->with('success', __('affiliations.status_updated_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update affiliation status: ' . $e->getMessage());

            return back()->with('error', __('affiliations.status_update_failed'));
        }
    }

    public function destroy(Affiliation $affiliation): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Store affiliation data for logging before deletion
            $affiliationData = [
                'id' => $affiliation->id,
                'member_type' => $affiliation->member_type,
                'member_id' => $affiliation->member_id,
                'member_name' => $affiliation->member?->name ?? 'Unknown',
                'federation_id' => $affiliation->federation_id,
                'federation_name' => $affiliation->federation?->name ?? 'Unknown',
                'start_date' => $affiliation->start_date?->format('Y-m-d'),
                'end_date' => $affiliation->end_date?->format('Y-m-d'),
                'status' => $affiliation->status_class,
                'individual_fee' => $affiliation->individual_fee,
                'entity_fee' => $affiliation->entity_fee,
                'requester_type' => $affiliation->requester_type,
                'requester_id' => $affiliation->requester_id,
            ];

            // Log the activity before deletion
            $logDescription = sprintf(
                'Deleted affiliation for %s (%s) with %s',
                $affiliation->member?->name ?? 'Unknown',
                class_basename($affiliation->member_type),
                $affiliation->federation?->name ?? 'Unknown Federation'
            );

            activity()
                ->performedOn($affiliation)
                ->causedBy(Auth::user())
                ->withProperties($affiliationData)
                ->log($logDescription);

            // Delete the affiliation
            $affiliation->delete();

            DB::commit();

            return redirect()->route('admin.affiliations.index')
                ->with('success', __('affiliations.deleted_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete affiliation: ' . $e->getMessage());

            return back()->with('error', __('affiliations.delete_failed'));
        }
    }
}
