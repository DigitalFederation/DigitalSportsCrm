<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Actions\SyncIndividualLocalFederationsAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\PendingFromEntityIndividualEntityState;
use Domain\Individuals\States\PendingFromIndividualEntityState;
use Domain\Individuals\States\PendingIndividualEntityState;
use Domain\Memberships\Services\MemberNumberService;
use Domain\Users\Actions\SyncUserRolesAction;
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

class IndividualApproveController extends Controller
{
    public function index(): View
    {
        $entityId = Auth::user()->getEntityId();

        $individuals = QueryBuilder::for(Individual::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::scope('filter_email'),
            ])
            ->whereHas('individualEntities', function (Builder $query) use ($entityId) {
                return $query->where('entity_id', $entityId)
                    ->whereIn('status_class', [
                        PendingFromIndividualEntityState::class,
                        PendingFromEntityIndividualEntityState::class,
                        PendingIndividualEntityState::class,
                    ]);
            })
            ->with(['country', 'individualEntities' => function ($query) use ($entityId) {
                $query->where('entity_id', $entityId);
            }])
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();

        return view('web.entity.individual.approve.index', compact('individuals', 'countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $memberNumberService = new MemberNumberService;

        try {
            DB::beginTransaction();
            $entityId = Auth::user()->getEntityId();

            $individual = Individual::where('id', $request->id)->whereHas('IndividualEntities', function (Builder $query) use ($entityId) {
                return $query->where('entity_id', $entityId)
                    ->whereIn('status_class', [PendingIndividualEntityState::class, PendingFromEntityIndividualEntityState::class]);
            })->firstOrFail();

            $individual->individualEntities()->update([
                'status_class' => ActiveIndividualEntityState::class,
            ]);

            // Assign member number to the individual
            $memberNumberService->assignIndividualMemberNumber($individual);

            // Sync individual to entity's local federations
            $entity = Entity::find($entityId);
            $syncAction = new SyncIndividualLocalFederationsAction;
            $syncAction->execute($individual, $entity);

            // Sync user roles so individual-approved is assigned
            if ($individual->user) {
                $syncRolesAction = new SyncUserRolesAction;
                $syncRolesAction->execute($individual->user);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getCode().': '.$e->getMessage());

            return redirect()->route('entity.individual-approve.index')->with('error', 'Error accepting the request');
        }

        return redirect()->route('entity.individual.index')->with('success', $individual->name.'\'s request to join accepted');
    }
}
