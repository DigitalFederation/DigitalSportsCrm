<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Notifications\UserAlert;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityFederation;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Entities\States\PendingEntityFederationState;
use Domain\Entities\States\RejectedEntityFederationState;
use Domain\Memberships\Services\MemberNumberService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EntityRequestController extends Controller
{
    public function index(): View
    {
        $entities = QueryBuilder::for(Entity::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::scope('filter_email'),
            ])
            ->whereHas('entityFederations', function (Builder $query) {
                return $query->where('federation_id', auth()->user()->federations()->first()->id)
                    ->where(function ($query) {
                        $query->where('status_class', PendingEntityFederationState::class);
                    })
                    ->whereNull('rejected_at');
            })
            ->with('country', 'entityFederations')
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();

        return view('web.federation.entity.request.index', compact('entities', 'countries'));
    }

    public function accept(Request $request, Entity $entity): RedirectResponse
    {
        $memberNumberService = new MemberNumberService;

        try {
            DB::beginTransaction();

            $loggedInFederation = auth()->user()->federations()->first();
            $isPrimaryFederation = $loggedInFederation->parent_id === null;

            $entityFederation = $entity->entityFederations()
                ->where('federation_id', $loggedInFederation->id)
                ->first();

            if (! empty($entityFederation)) {
                $updateData = [
                    'active' => true,
                    'status_class' => ActiveEntityFederationState::class,
                ];

                // Only the main federation can assign national_federation_number.
                if ($isPrimaryFederation) {
                    // Assign member number to the entity first
                    $memberNumberService->assignEntityMemberNumber($entity);

                    // Use the entity's member_number as the national_federation_number
                    // Refresh to get the newly assigned member_number
                    $entity->refresh();
                    if ($entity->member_number) {
                        $updateData['national_federation_number'] = (string) $entity->member_number;
                    }
                }

                $entityFederation->update($updateData);
            }

            if (! empty($entity->user()->first())) {
                $federationName = auth()->user()->federations()->first()->name;
                $entity->user()->first()->notify(new UserAlert(__('notifications.request_approved', ['federation' => $federationName])));
            }

            DB::commit();

            return redirect()->back()->with('success', __('notifications.association_request_accepted'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getCode().': '.$e->getMessage());

            return redirect()->back()->with('error', 'Error accepting the request');
        }

    }

    public function reject(Request $request, $entityId)
    {
        try {
            DB::beginTransaction();

            $entityFederation = EntityFederation::where('entity_id', $entityId)
                ->where('federation_id', auth()->user()->federations()->first()->id)
                ->firstOrFail();

            $entityFederation->update([
                'active' => false,
                'status_class' => RejectedEntityFederationState::class,
                'rejected_at' => now(),
            ]);

            activity('Entity Request')
                ->performedOn($entityFederation)
                ->event('rejected')
                ->withProperties($entityFederation->toArray())
                ->log('Entity request was rejected:'.$entityFederation->entity->name);

            DB::commit();

            return redirect()->back()->with('success', 'Entity request rejected successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);

            return redirect()->back()->with('error', 'Failed to reject the entity request.');
        }
    }
}
