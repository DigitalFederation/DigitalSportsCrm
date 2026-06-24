<?php

namespace Domain\Federations\Actions;

use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateIndividualEmailAction
{
    public function execute(Individual $individual, string $newPublicEmail, ?string $newLoginEmail = null): bool
    {
        try {
            DB::beginTransaction();

            $oldPublicEmail = $individual->email;
            $individual->email = $newPublicEmail;
            $individual->save();

            $changes = [
                'old_public_email' => $oldPublicEmail,
                'new_public_email' => $newPublicEmail,
                'individual_id' => $individual->id,
            ];

            if ($newLoginEmail !== null) {
                $user = $individual->user;
                $oldLoginEmail = $user->email;
                $user->email = $newLoginEmail;
                $user->save();

                $changes['old_login_email'] = $oldLoginEmail;
                $changes['new_login_email'] = $newLoginEmail;
                $changes['user_id'] = $user->id;
            }

            activity()
                ->performedOn($individual)
                ->causedBy(auth()->user())
                ->withProperties($changes)
                ->log('Individual email updated' . ($newLoginEmail !== null ? ' (including login email)' : ''));

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update individual email: ' . $e->getMessage());

            return false;
        }
    }
}
