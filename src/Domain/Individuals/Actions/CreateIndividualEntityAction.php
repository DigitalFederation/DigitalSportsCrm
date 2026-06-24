<?php

namespace Domain\Individuals\Actions;

use App\Notifications\EntityMemberInvitationNotification;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Entities\States\PendingEntityFederationState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingFromIndividualEntityState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Illuminate\Support\Facades\Log;

class CreateIndividualEntityAction
{
    /**
     * @throws \Exception
     */
    public function execute(?string $member_code, int $entityId, ?string $member_number = null): ?IndividualEntity
    {
        $individual = $member_code
            ? Individual::where('member_code', $member_code)->first()
            : Individual::where('member_number', $member_number)->first();

        if (! $individual) {
            return null;
        }

        // Get the federation IDs associated with the entity
        $entityFederationIds = Entity::find($entityId)
            ->entityFederations()
            ->whereIn('status_class', [
                ActiveEntityFederationState::class,
                PendingEntityFederationState::class,
            ])
            ->pluck('federation_id');

        // Check if individual has Active OR Pending relationship with these federations
        $hasValidFederationRelationship = $individual->individualFederations()
            ->whereIn('federation_id', $entityFederationIds)
            ->whereIn('status_class', [
                ActiveIndividualFederationState::class,
                PendingIndividualFederationState::class,
            ])
            ->exists();

        if (! $hasValidFederationRelationship) {
            return null;
        }

        // Check if individual is already associated with this entity
        $existingRelationship = IndividualEntity::where('individual_id', $individual->id)
            ->where('entity_id', $entityId)
            ->exists();

        if ($existingRelationship) {
            return null;
        }

        // Create the invitation
        $individualEntity = IndividualEntity::create([
            'individual_id' => $individual->id,
            'entity_id' => $entityId,
            'status_class' => PendingFromIndividualEntityState::class,
        ]);

        $entity = Entity::find($entityId);

        // Log the activity
        activity()
            ->performedOn($individualEntity)
            ->causedBy(auth()->user())
            ->withProperties([
                'individual_id' => $individual->id,
                'individual_name' => $individual->full_name,
                'entity_id' => $entityId,
                'entity_name' => $entity->name,
                'status' => 'pending_from_individual',
            ])
            ->log('create_invitation');

        // Send notification to the individual
        try {
            $userToNotify = $individual->user;
            if ($userToNotify) {
                $userToNotify->notify(new EntityMemberInvitationNotification($entity));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send entity member invitation notification: ' . $e->getMessage());
        }

        return $individualEntity;
    }
}
