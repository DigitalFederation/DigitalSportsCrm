<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipRequest;
use App\Models\Committee;
use App\Models\Country;
use App\Models\Sport;
use App\Notifications\CreateMembershipNotification;
use Domain\Documents\Actions\CreateDocumentWithDetailsAction;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Actions\ActivateMembershipAction;
use Domain\Memberships\Actions\BuildMembershipDocumentDetailAction;
use Domain\Memberships\Actions\CreateMembershipAction;
use Domain\Memberships\Actions\DeleteMembershipAction;
use Domain\Memberships\Actions\EditMembershipAction;
use Domain\Memberships\DataTransferObject\MembershipData;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $memberships = QueryBuilder::for(Membership::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_federation'),
                AllowedFilter::scope('filter_federation_code'),
                AllowedFilter::scope('filter_status', 'membership_status'),
                AllowedFilter::scope('filter_sport'),
                AllowedFilter::scope('filter_committee'),
                AllowedFilter::scope('filter_country'),
            ])
            ->with('federation', 'plans')
            ->paginate()
            ->appends(request()->query());

        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Suspended')],
        ];

        return view('web.admin.membership.index', compact('federations', 'memberships', 'sports', 'committees', 'filter_status', 'countries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $federations = Federation::select('id', 'name')->whereNull('parent_id')->orderBy('name')->get();
        $plans = MembershipPlan::select('id', 'name')->orderBy('name')->pluck('name', 'id');

        return view('web.admin.membership.create', compact('federations', 'plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        MembershipRequest $request,
        CreateMembershipAction $createMembershipAction,
        CreateDocumentWithDetailsAction $createDocumentAction,
        BuildMembershipDocumentDetailAction $buildMembershipDocumentAction
    ): RedirectResponse {

        try {
            db::beginTransaction();

            $membership = $createMembershipAction(MembershipData::fromArray($request->validated()));

            if (! empty($membership)) {
                // Add membership name to notes
                $notes = 'Reference to: ' . $membership->name;

                if ($request->input('blockDocument') == null || ! auth()->user()->isAdmin()) {
                    $document = $createDocumentAction(
                        $buildMembershipDocumentAction($membership),
                        'ORD',
                        $membership->federation_id,
                        Federation::class,
                        $notes
                    );

                    if ($document === null) {
                        Log::info('No document created for membership - total value is zero', [
                            'membership_id' => $membership->id,
                            'federation_id' => $membership->federation_id,
                        ]);
                    }
                }

                db::commit();

                foreach ($membership->federation->users as $user) {
                    $user->notify(new CreateMembershipNotification);
                }

                activity('Membership')
                    ->performedOn($membership)
                    ->event('created')
                    ->withProperties($membership->toArray())
                    ->log('Membership was created');

                return redirect(route('admin.membership.index'))->with('success', 'Membership created with success.');
            }
        } catch (Exception $ex) {
            db::rollBack();
            Log::error($ex->getMessage());

            return back()->with('error', 'Error creating a membership. ' . $ex->getMessage());
        }
        db::rollBack();
        Log::error('Federation don\'t was deleted but there is no errors.');

        return back()->with('error', 'Error creating a membership.');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $membership = Membership::with('federation', 'plans.licenses.type', 'plans.licenses.committee', 'plans.licenses.sport', 'plans.products')->findOrFail($id);

        $licenses = collect();
        foreach ($membership->plans as $plan) {
            foreach ($plan->licenses as $license) {
                $licenses->push($license);
            }
        }

        $licenses = $licenses->unique('id');

        return view('web.admin.membership.show', compact('membership', 'licenses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $membership = Membership::with('plans')->findOrFail($id);
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $plans = MembershipPlan::select('id', 'name')->orderBy('name')->pluck('name', 'id');

        return view('web.admin.membership.edit', compact('membership', 'federations', 'plans'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(MembershipRequest $membershipRequest, int $id, EditMembershipAction $editMembershipAction): RedirectResponse
    {
        try {
            $edit = $editMembershipAction(MembershipData::fromArray($membershipRequest->validated()), $id);

            if ($edit) {
                return redirect(route('admin.membership.show', $id))->with('success', 'Membership updated with success.');
            } else {
                return back()->with('error', 'Error updating the membership.');
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error updating the membership, ' . $ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function destroy(int $id, DeleteMembershipAction $deleteMembershipAction): RedirectResponse
    {
        try {
            $deleted = $deleteMembershipAction($id);

            if ($deleted) {
                return back()->with('success', __('Membership deleted.'));
            } else {
                Log::error('Membership wasn\'t deleted but there is no errors.');

                return back()->with('error', __('Error creating this record, please contact the administrator.'));
            }
        } catch (Exception $ex) {
            switch ($ex->getCode()) {
                case 802:
                    // This membership can't be deleted because it has already been activated.
                    return back()->with('error', $ex->getMessage());

                default:
                    Log::error($ex->getCode() . ': ' . $ex->getMessage());

                    return back()->with('error', __('Error creating this record, please contact the administrator.'));
            }
        }
    }

    public function activate(int $id, ActivateMembershipAction $action): RedirectResponse
    {
        try {
            db::beginTransaction();
            $action($id);
            db::commit();
        } catch (Exception $ex) {
            db::rollBack();
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', __('Error activating this record, please contact the administrator.'));
        }

        return back()->with('success', __('Membership activated.'));
    }
}
