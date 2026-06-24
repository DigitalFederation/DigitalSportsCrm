<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Notifications\EntityMemberAcceptedNotification;
use App\Notifications\EntityRequestNotification;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Individuals\Actions\SyncIndividualLocalFederationsAction;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\PendingFromEntityIndividualEntityState;
use Domain\Individuals\States\PendingFromIndividualEntityState;
use Domain\Individuals\States\PendingIndividualEntityState;
use Domain\Users\Actions\SyncUserRolesAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EntityController extends Controller
{
    public function index(): View
    {
        $individual = auth()->user()->individual;

        // Get associated entities with constrained eager loading for the current individual
        $associatedEntities = Entity::whereHas('individuals', function ($query) use ($individual) {
            $query->where('individual_id', $individual->id);
        })
            ->with([
                'individualEntities' => function ($query) use ($individual) {
                    $query->where('individual_id', $individual->id);
                },
                'country.geoZone',
                'country.subRegion',
            ])
            ->get();

        // Get all available entities with active federation status
        // No longer filtering by specific federation - showing all entities
        $entities = Entity::whereHas('federations', function ($query) {
            $query->where('status_class', ActiveEntityFederationState::class);
        })
            ->whereDoesntHave('individuals', function ($query) use ($individual) {
                $query->where('individual_id', $individual->id)
                    ->where('status_class', ActiveIndividualEntityState::class);
            })
            ->with(['country.geoZone', 'country.subRegion', 'federations' => function ($query) {
                $query->where('status_class', ActiveEntityFederationState::class);
            }])
            ->get();

        return view('web.individual.entity.index', compact('entities', 'associatedEntities', 'individual'));
    }

    public function show(Entity $entity): View
    {
        $entity->load(['federations' => function ($query) {
            $query->where('status_class', ActiveEntityFederationState::class);
        }]);

        return view('web.individual.entity.show', compact('entity'));
    }

    public function store(Request $request): RedirectResponse
    {
        $individual = auth()->user()->individuals()->first();
        $entity = Entity::whereHas('federations', function ($query) {
            $query->where('status_class', ActiveEntityFederationState::class);
        })->findOrFail($request->entity_id);

        // Check if the individual is already associated with the entity
        if ($entity->individuals()->where('individual_id', $individual->id)->exists()) {
            return redirect()->route('individual.entity.index')->with('error', 'You are already associated with this entity.');
        }

        $individual->entities()->attach($entity->id, [
            'status_class' => PendingFromEntityIndividualEntityState::class,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        activity()
            ->performedOn($individual)
            ->causedBy(Auth::user())
            ->withProperties(['entity_id' => $entity->id, 'individual_id' => $individual->id])
            ->log('Invitation to entity sent');

        // Trigger the notification
        $usersToNotify = $entity->users;
        foreach ($usersToNotify as $user) {
            $user->notify(new EntityRequestNotification($individual, $entity));
        }

        return redirect()->route('individual.entity.index')->with('success', 'Invitation request sent. Please wait for the entity to approve your request.');
    }

    public function destroy(Entity $entity): RedirectResponse
    {
        $individual = auth()->user()->individuals()->first();

        // Remove individual from entity's local federations before detaching
        $syncAction = new SyncIndividualLocalFederationsAction;
        $syncAction->removeOnDeactivation($individual, $entity);

        $entity->individuals()->detach($individual->id);

        return redirect()->route('individual.entity.index')->with('success', 'The relationship with the entity was successfully removed.');
    }

    public function approve(Request $request): RedirectResponse
    {
        $individualId = auth()->user()->individual->id;
        $entityId = $request->id;

        try {
            DB::beginTransaction();

            // Fetch the specific IndividualEntity record
            $individualEntity = IndividualEntity::where('individual_id', $individualId)
                ->where('entity_id', $entityId)
                ->whereIn('status_class', [
                    PendingIndividualEntityState::class,
                    PendingFromIndividualEntityState::class,
                ])->firstOrFail();

            // Update the status of this specific individual entity relation
            $individualEntity->update([
                'status_class' => ActiveIndividualEntityState::class,
            ]);

            // Sync individual to entity's local federations
            $individual = auth()->user()->individual;
            $entity = Entity::find($entityId);
            $syncAction = new SyncIndividualLocalFederationsAction;
            $syncAction->execute($individual, $entity);

            // Sync user roles so individual-approved is assigned
            $syncRolesAction = new SyncUserRolesAction;
            $syncRolesAction->execute(auth()->user());

            DB::commit();

            // Send notification to entity users (outside transaction)
            try {
                $usersToNotify = $entity->users;
                foreach ($usersToNotify as $user) {
                    $user->notify(new EntityMemberAcceptedNotification($individual, $entity));
                }
            } catch (Exception $e) {
                Log::warning('Failed to send member accepted notification: ' . $e->getMessage());
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getCode() . ': ' . $e->getMessage());

            return redirect()->route('individual.entity.index')->with('error', 'Error accepting the request');
        }

        return redirect()->route('individual.entity.index')->with('success', "{$individualEntity->entity->name}'s request to join accepted");
    }
}
