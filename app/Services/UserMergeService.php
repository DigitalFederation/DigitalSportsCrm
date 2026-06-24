<?php

namespace App\Services;

use App\Models\User;
use Domain\Users\Actions\MergeUserAccountsAction;

class UserMergeService
{
    protected $mergeAction;

    public function __construct(MergeUserAccountsAction $mergeAction)
    {
        $this->mergeAction = $mergeAction;
    }

    public function previewMerge(array $data): array
    {
        $sourceUser = User::with([
            'roles',
            'federations',
            'entities',
            'individual',
            'individual.certificationsAttributed',
            'individual.licenses',
        ])
            ->where('email', $data['source_email'])
            ->firstOrFail();

        $targetUser = User::with([
            'roles',
            'federations',
            'entities',
            'individual',
        ])
            ->where('email', $data['target_email'])
            ->firstOrFail();

        return [
            'source' => [
                'id' => $sourceUser->id,
                'name' => $sourceUser->name,
                'email' => $sourceUser->email,
                'roles' => $sourceUser->roles->pluck('name'),
                'federations' => $sourceUser->federations->pluck('name'),
                'entities' => $sourceUser->entities->pluck('name'),
                'individual' => $sourceUser->individual ? [
                    'id' => $sourceUser->individual->id,
                    'name' => $sourceUser->individual->full_name,
                    'certifications' => $sourceUser->individual->certificationsAttributed->pluck('certification_name'),
                    'licenses' => $sourceUser->individual->licenses->pluck('license_name'),
                ] : null,
            ],
            'target' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'roles' => $targetUser->roles->pluck('name'),
                'federations' => $targetUser->federations->pluck('name'),
                'entities' => $targetUser->entities->pluck('name'),
                'individual' => $targetUser->individual ? [
                    'id' => $targetUser->individual->id,
                    'name' => $targetUser->individual->full_name,
                ] : null,
            ],
            'mergeDetails' => $this->getMergeDetails($sourceUser, $targetUser),
        ];
    }

    public function mergeAccounts(array $data): array
    {
        $sourceUser = User::where('email', $data['source_email'])->firstOrFail();
        $targetUser = User::where('email', $data['target_email'])->firstOrFail();

        $individualChoice = $data['individual_choice'] ?? null;

        $success = $this->mergeAction->execute($sourceUser, $targetUser, $individualChoice);

        return [
            'success' => $success,
            'message' => $success ? 'Users merged successfully' : 'Failed to merge users',
        ];
    }

    private function getMergeDetails(User $sourceUser, User $targetUser): array
    {
        $details = [];

        if ($sourceUser->roles->isNotEmpty()) {
            $details[] = 'Roles will be combined';
        }

        if ($sourceUser->federations->isNotEmpty()) {
            $details[] = 'Federations will be combined';
        }

        if ($sourceUser->entities->isNotEmpty()) {
            $details[] = 'Entities will be combined';
        }

        if ($sourceUser->individual && $targetUser->individual) {
            $details[] = 'Both users have an individual. Please select which individual to keep.';
        } elseif ($sourceUser->individual || $targetUser->individual) {
            $details[] = 'Individual will be transferred to the target user.';
        } else {
            $details[] = 'No individuals associated with the users.';
        }

        return $details;
    }

}
