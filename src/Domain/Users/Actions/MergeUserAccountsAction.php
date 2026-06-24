<?php

namespace Domain\Users\Actions;

use App\Models\User;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MergeUserAccountsAction
{
    public function execute(User $sourceUser, User $targetUser, string $individualChoice): bool
    {
        try {
            DB::beginTransaction();

            // Transfer relationships
            $this->transferRelationships($sourceUser, $targetUser, $individualChoice);

            // Log the merge operation
            activity()
                ->performedOn($sourceUser)
                ->withProperties([
                    'merged_into' => $targetUser->id,
                    'source_email' => $sourceUser->email,
                    'target_email' => $targetUser->email,
                ])
                ->log('Merged user accounts');

            // Force delete the source user to remove completely
            $sourceUser->delete();
            $sourceUser->federations()->detach();
            $sourceUser->entities()->detach();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error merging user accounts: ' . $e->getMessage());

            return false;
        }
    }

    private function transferRelationships(User $sourceUser, User $targetUser, ?string $individualChoice): void
    {
        // Transfer federations
        $targetUser->federations()->syncWithoutDetaching($sourceUser->federations->pluck('id'));

        // Transfer entities
        $targetUser->entities()->syncWithoutDetaching($sourceUser->entities->pluck('id'));

        // Handle individuals
        if ($sourceUser->individual && $targetUser->individual) {
            if ($individualChoice === 'source') {
                // Delete target user's individual
                $targetUser->individual->forceDelete();

                // Transfer source user's individual to target user
                $sourceUser->individual->user_id = (string) $targetUser->id;
                $sourceUser->individual->save();

                // Transfer related data
                $this->transferIndividualData($sourceUser->individual, $targetUser->individual);
            } elseif ($individualChoice === 'target') {
                // Keep target user's individual, delete source user's individual
                $sourceUser->individual->forceDelete();
            } else {
                // Default action: keep target user's individual and delete source's
                $sourceUser->individual->forceDelete();
            }
        } elseif ($sourceUser->individual) {
            // Transfer source user's individual to target user
            $sourceUser->individual->user_id = (string) $targetUser->id;
            $sourceUser->individual->save();

            // Transfer related data
            $this->transferIndividualData($sourceUser->individual, null);
        }

        // Transfer roles
        $roles = $sourceUser->roles->pluck('name')->merge($targetUser->roles->pluck('name'))->unique();
        $targetUser->syncRoles($roles);
    }

    private function transferIndividualData(Individual $sourceIndividual, ?Individual $targetIndividual): void
    {
        $newIndividualId = $targetIndividual ? $targetIndividual->id : $sourceIndividual->id;

        // Transfer certifications
        CertificationAttributed::where('individual_id', $sourceIndividual->id)
            ->update(['individual_id' => $newIndividualId]);

        // Transfer licenses
        LicenseAttributed::where('model_type', 'individual')
            ->where('model_id', $sourceIndividual->id)
            ->update(['model_id' => $newIndividualId]);
    }

    private function transferCertifications(User $sourceUser, User $targetUser): void
    {
        CertificationAttributed::where('individual_id', $sourceUser->individual->id)
            ->update(['individual_id' => $targetUser->individual->id]);
    }

    private function transferLicenses(User $sourceUser, User $targetUser): void
    {
        LicenseAttributed::where('model_type', 'individual')
            ->where('model_id', $sourceUser->individual->id)
            ->update(['model_id' => $targetUser->individual->id]);
    }
}
