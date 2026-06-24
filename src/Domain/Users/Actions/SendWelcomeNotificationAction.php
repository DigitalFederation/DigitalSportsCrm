<?php

namespace Domain\Users\Actions;

use App\Models\User;
use App\Notifications\CreatedEntityNotification;
use App\Notifications\CreatedFederationNotification;
use App\Notifications\CreatedIndividualNotification;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\Password;

class SendWelcomeNotificationAction
{
    public function execute(User $user): bool
    {
        $token = Password::createToken($user);

        if ($user->isFederation()) {
            return $this->sendFederationNotification($user, $token);
        }

        if ($user->isEntity()) {
            return $this->sendEntityNotification($user, $token);
        }

        if ($user->isIndividual()) {
            return $this->sendIndividualNotification($user, $token);
        }

        return false;
    }

    private function sendFederationNotification(User $user, string $token): bool
    {
        $federation = $user->federations()->first();

        if (! $federation instanceof Federation) {
            return false;
        }

        $user->notify(new CreatedFederationNotification($federation, $token));
        $user->update(['welcome_email_sent_at' => now()]);

        return true;
    }

    private function sendEntityNotification(User $user, string $token): bool
    {
        $entity = $user->entities()->first();

        if (! $entity instanceof Entity) {
            return false;
        }

        $user->notify(new CreatedEntityNotification($entity, $token));
        $user->update(['welcome_email_sent_at' => now()]);

        return true;
    }

    private function sendIndividualNotification(User $user, string $token): bool
    {
        $individual = $user->individual;

        if (! $individual instanceof Individual) {
            return false;
        }

        $user->notify(new CreatedIndividualNotification($individual, $token));
        $user->update(['welcome_email_sent_at' => now()]);

        return true;
    }
}
