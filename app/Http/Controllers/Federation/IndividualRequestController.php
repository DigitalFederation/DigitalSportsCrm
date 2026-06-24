<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Notifications\UserAlert;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Domain\Individuals\States\RejectedIndividualFederationState;
use Domain\Memberships\Services\MemberNumberService;
use Domain\Users\Actions\SyncUserRolesAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndividualRequestController extends Controller
{
    public function index(): View
    {
        $federationId = auth()->user()->federations()->first()->id;

        $individuals = QueryBuilder::for(Individual::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_surname'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::callback('filter_status', function (Builder $query, $value) use ($federationId) {
                    $stateMap = [
                        'pending' => PendingIndividualFederationState::class,
                        'rejected' => RejectedIndividualFederationState::class,
                    ];
                    if (isset($stateMap[$value])) {
                        $query->whereHas('individualFederations', function (Builder $q) use ($federationId, $stateMap, $value) {
                            $q->where('federation_id', $federationId)
                                ->where('status_class', $stateMap[$value]);
                        });
                    }
                }),
            ])
            ->with(['country', 'individualFederations' => function ($query) use ($federationId) {
                $query->where('federation_id', $federationId)
                    ->whereIn('status_class', [
                        PendingIndividualFederationState::class,
                        RejectedIndividualFederationState::class,
                    ]);
            }])
            ->when(! request()->has('filter.filter_status'), function ($query) use ($federationId) {
                $query->whereHas('individualFederations', function (Builder $q) use ($federationId) {
                    $q->where('federation_id', $federationId)
                        ->where('status_class', PendingIndividualFederationState::class);
                });
            })
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();

        $statuses = collect([
            'pending' => __('main.pending'),
            'rejected' => __('main.rejected'),
        ]);

        return view('web.federation.individual.request.index', compact('individuals', 'countries', 'statuses'));
    }

    public function accept(Request $request, string $id): RedirectResponse
    {
        $syncUserRolesAction = new SyncUserRolesAction;
        $memberNumberService = new MemberNumberService;

        try {
            DB::beginTransaction();

            $individualFederation = IndividualFederation::where('id', $id)
                ->where('federation_id', auth()->user()->federations()->first()->id)
                ->where('status_class', PendingIndividualFederationState::class)
                ->firstOrFail();

            $individualFederation->update([
                'active' => true,
                'status_class' => ActiveIndividualFederationState::class,
            ]);

            $individual = $individualFederation->individual;

            // Save national_federation_number on the individual record
            if ($request->filled('national_federation_number')) {
                $individual->update([
                    'national_federation_number' => $request->national_federation_number,
                ]);
            }

            $memberNumberService->assignIndividualMemberNumber($individual);

            $syncUserRolesAction->execute($individual->user);

            $individual->user->notify(new UserAlert(__('notifications.federation_request_approved')));

            activity('Individual Request')
                ->performedOn($individualFederation)
                ->event('accepted')
                ->withProperties($individualFederation->toArray())
                ->log('Individual request was accepted: '.$individual->full_name);

            DB::commit();

            return redirect()->back()->with('success', __('notifications.request_join_accepted', ['name' => $individual->name]));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', __('notifications.error_accepting_request'));
        }
    }

    public function reject(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $individualFederation = IndividualFederation::where('id', $id)
                ->where('federation_id', auth()->user()->federations()->first()->id)
                ->where('status_class', PendingIndividualFederationState::class)
                ->firstOrFail();

            $individualFederation->update([
                'active' => false,
                'status_class' => RejectedIndividualFederationState::class,
                'rejected_at' => now(),
            ]);

            activity('Individual Request')
                ->performedOn($individualFederation)
                ->event('rejected')
                ->withProperties($individualFederation->toArray())
                ->log('Individual request was rejected: '.$individualFederation->individual->full_name);

            DB::commit();

            return redirect()->back()->with('success', __('notifications.request_rejected'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', __('notifications.error_rejecting_request'));
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $individualFederation = IndividualFederation::where('id', $id)
                ->where('federation_id', auth()->user()->federations()->first()->id)
                ->where('status_class', PendingIndividualFederationState::class)
                ->firstOrFail();
            $individual = $individualFederation->individual;

            $individualFederation->delete();

            activity('Individual Request')
                ->performedOn($individualFederation)
                ->event('deleted')
                ->withProperties($individualFederation->toArray())
                ->log('Individual request was deleted: '.$individual->full_name);

            return redirect()->back()->with('success', __('notifications.request_deleted'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
