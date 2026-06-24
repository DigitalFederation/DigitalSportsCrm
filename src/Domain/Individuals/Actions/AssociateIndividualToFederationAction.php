<?php

namespace Domain\Individuals\Actions;

use App\Notifications\FederationIndividualRequestToJoinNotification;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\PendingIndividualFederationState;
use Illuminate\Support\Facades\Notification;

class AssociateIndividualToFederationAction
{
    public function __invoke(Individual $individual, Federation $federation): Individual
    {
        $federations = $individual->federations();
        $federations->attach($federation->id, [
            'active' => false,
            'status_class' => PendingIndividualFederationState::class,
            'created_at' => now(),
            'updated_at' => now()]
        );

        if ($federations->whereKey($federation->id)->exists()) {
            activity('Individual To Federation')
                ->performedOn($individual)
                ->event('associate')
                ->withProperties($federation->toArray())
                ->log('Individual associated to federation '.$federation->name);

            Notification::send($federation->users()->get(), new FederationIndividualRequestToJoinNotification($individual));
        }

        return $individual;
    }
}
