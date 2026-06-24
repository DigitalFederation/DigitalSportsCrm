<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Notifications\EntityRequestNotification;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\PendingFromEntityIndividualEntityState;
use Domain\Individuals\States\PendingFromIndividualEntityState;
use Domain\Individuals\States\PendingIndividualEntityState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DivingEntitiesController extends Controller
{
    public function index(): View
    {
        $individual = auth()->user()->individual;

        // Get only diving-related entity invitations (either through diving licenses OR diving professional roles)
        $pendingInvitations = IndividualEntity::where('individual_id', $individual->id)
            ->whereIn('status_class', [
                PendingIndividualEntityState::class,
                PendingFromIndividualEntityState::class,
            ])
            ->where(function ($query) use ($individual) {
                // Either has diving licenses
                $query->whereHas('entity', function ($entityQuery) {
                    $entityQuery->whereHas('licenses', function ($q) {
                        $q->whereHas('license', function ($licenseQuery) {
                            $licenseQuery->where('committee_id', 3); // Diving committee
                        });
                    });
                })
                // OR has pending diving professional invitation
                    ->orWhereHas('entity', function ($entityQuery) use ($individual) {
                        $entityQuery->whereHas('entityProfessionals', function ($roleQuery) use ($individual) {
                            $roleQuery->where('individual_id', $individual->id)
                                ->where('status_class', \Domain\Entities\States\PendingEntityProfessionalRoleState::class)
                                ->whereHas('professionalRole', function ($profQuery) {
                                    $profQuery->where('role', 'DIVINGPROFESSIONAL')
                                        ->where('committee_id', function ($q) {
                                            $q->select('id')
                                                ->from('committee')
                                                ->where('code', 'DIVING');
                                        });
                                });
                        });
                    });
            })
            ->with('entity')
            ->get();

        // Get active diving entity associations (either through diving licenses OR diving professional roles OR technical director roles)
        $activeAssociations = IndividualEntity::where('individual_id', $individual->id)
            ->where('status_class', ActiveIndividualEntityState::class)
            ->where(function ($query) use ($individual) {
                // Either has diving licenses
                $query->whereHas('entity', function ($entityQuery) {
                    $entityQuery->whereHas('licenses', function ($q) {
                        $q->whereHas('license', function ($licenseQuery) {
                            $licenseQuery->where('committee_id', 3); // Diving committee
                        });
                    });
                })
                // OR is associated as a diving professional
                    ->orWhereHas('entity', function ($entityQuery) use ($individual) {
                        $entityQuery->whereHas('entityProfessionals', function ($roleQuery) use ($individual) {
                            $roleQuery->where('individual_id', $individual->id)
                                ->where('status_class', \Domain\Entities\States\ActiveEntityProfessionalRoleState::class)
                                ->whereHas('professionalRole', function ($profQuery) {
                                    $profQuery->where('role', 'DIVINGPROFESSIONAL')
                                        ->where('committee_id', function ($q) {
                                            $q->select('id')
                                                ->from('committee')
                                                ->where('code', 'DIVING');
                                        });
                                });
                        });
                    });
            })
            ->with(['entity', 'entity.licenses.license'])
            ->get();

        // Get available diving entities to join
        $availableEntities = Entity::whereHas('licenses', function ($query) {
            $query->whereHas('license', function ($licenseQuery) {
                $licenseQuery->where('committee_id', 3); // Diving committee
            });
        })
            ->whereHas('federations', function ($query) {
                $query->where('status_class', ActiveEntityFederationState::class);
            })
            ->whereDoesntHave('individuals', function ($query) use ($individual) {
                $query->where('individual_id', $individual->id);
            })
            ->with(['country.geoZone', 'country.subRegion'])
            ->get();

        return view('web.individual.diving_entities.index', compact(
            'pendingInvitations',
            'activeAssociations',
            'availableEntities',
            'individual'
        ));
    }

    public function show(Entity $entity): View
    {
        $individual = auth()->user()->individuals()->first();

        // Check if the individual has access to view this entity
        // Either through being a technical director or having a diving entity association
        $hasAccess = false;

        // Check if entity has diving licenses
        $hasDivingLicenses = $entity->licenses()->whereHas('license', function ($query) {
            $query->whereHas('committee', function ($q) {
                $q->where('code', 'DIVING');
            });
        })->exists();

        if ($hasDivingLicenses) {
            $hasAccess = true;
        }

        // Also check if the individual is a technical director for this entity
        if (! $hasAccess && $individual) {
            $isTechnicalDirector = \Domain\Diving\Models\DivingEntityTechnicalDirector::where('entity_id', $entity->id)
                ->where('individual_id', $individual->id)
                ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
                ->exists();

            if ($isTechnicalDirector) {
                $hasAccess = true;
            }
        }

        if (! $hasAccess) {
            abort(404);
        }

        $entity->load(['federations' => function ($query) {
            $query->where('status_class', ActiveEntityFederationState::class);
        }]);

        return view('web.individual.diving_entities.show', compact('entity'));
    }

    public function store(Request $request): RedirectResponse
    {
        $individual = auth()->user()->individual;
        $entity = Entity::whereHas('licenses', function ($query) {
            $query->whereHas('license', function ($licenseQuery) {
                $licenseQuery->where('committee_id', 3); // Diving committee
            });
        })
            ->whereHas('federations', function ($query) {
                $query->where('status_class', ActiveEntityFederationState::class);
            })
            ->findOrFail($request->entity_id);

        // Check if the individual is already associated with the entity
        if ($entity->individuals()->where('individual_id', $individual->id)->exists()) {
            return redirect()->route('individual.diving_entities.index')
                ->with('error', __('diving.already_associated_with_entity'));
        }

        $individual->entities()->attach($entity->id, [
            'status_class' => PendingFromIndividualEntityState::class,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        activity()
            ->performedOn($individual)
            ->causedBy(Auth::user())
            ->withProperties(['entity_id' => $entity->id, 'individual_id' => $individual->id])
            ->log('Diving entity invitation sent');

        // Trigger the notification
        $usersToNotify = $entity->users;
        foreach ($usersToNotify as $user) {
            $user->notify(new EntityRequestNotification($individual, $entity));
        }

        return redirect()->route('individual.diving_entities.index')
            ->with('success', __('diving.invitation_request_sent'));
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
                    PendingFromEntityIndividualEntityState::class,
                ])
                ->whereHas('entity', function ($query) {
                    $query->whereHas('licenses', function ($q) {
                        $q->whereHas('license', function ($licenseQuery) {
                            $licenseQuery->where('committee_id', 3); // Diving committee
                        });
                    });
                })
                ->firstOrFail();

            // Update the status of this specific individual entity relation
            $individualEntity->update([
                'status_class' => ActiveIndividualEntityState::class,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getCode() . ': ' . $e->getMessage());

            return redirect()->route('individual.diving_entities.index')
                ->with('error', __('diving.error_accepting_request'));
        }

        return redirect()->route('individual.diving_entities.index')
            ->with('success', __('diving.entity_request_accepted', ['entity' => $individualEntity->entity->name]));
    }

    public function reject(Request $request): RedirectResponse
    {
        $individualId = auth()->user()->individual->id;
        $entityId = $request->id;

        try {
            DB::beginTransaction();

            // Fetch and delete the specific IndividualEntity record
            $individualEntity = IndividualEntity::where('individual_id', $individualId)
                ->where('entity_id', $entityId)
                ->whereIn('status_class', [
                    PendingIndividualEntityState::class,
                    PendingFromEntityIndividualEntityState::class,
                ])
                ->whereHas('entity', function ($query) {
                    $query->whereHas('licenses', function ($q) {
                        $q->whereHas('license', function ($licenseQuery) {
                            $licenseQuery->where('committee_id', 3); // Diving committee
                        });
                    });
                })
                ->firstOrFail();

            $entityName = $individualEntity->entity->name;
            $individualEntity->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getCode() . ': ' . $e->getMessage());

            return redirect()->route('individual.diving_entities.index')
                ->with('error', __('diving.error_rejecting_request'));
        }

        return redirect()->route('individual.diving_entities.index')
            ->with('success', __('diving.entity_request_rejected', ['entity' => $entityName]));
    }

    public function destroy(Entity $entity): RedirectResponse
    {
        // Ensure entity is a diving entity
        if (! $entity->licenses()->whereHas('license', function ($query) {
            $query->where('committee_id', 3); // Diving committee
        })->exists()) {
            abort(404);
        }

        $entity->individuals()->detach(auth()->user()->individual->id);

        return redirect()->route('individual.diving_entities.index')
            ->with('success', __('diving.entity_relationship_removed'));
    }
}
