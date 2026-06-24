<?php

namespace App\Providers;

use App\Models\Committee;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class CustomFortifyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        Fortify::authenticateUsing(function (Request $request) {

            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                //Find if user has any Entity associated
                $entity = Entity::select('id')
                    ->whereHas('users', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    })
                    ->first();

                if (!empty($entity)) {
                    session()->put('commitees', $this->getCommitteeFromUser($entity->id, 'entity'));
                }

                $individual = Individual::select('id', 'user_id')->where('user_id', $user->id)->first();

                if (!empty($individual)) {
                    session()->put('committees', $this->getCommitteeFromUser($individual->id, 'individual'));
                }

                return $user;
            }
        });
        */
    }

    private function getCommitteeFromUser($model_id, $model_type): array
    {
        $model_type = match ($model_type) {
            'entity' => Entity::class,
            'federation' => Federation::class,
            default => Individual::class,
        };

        return Committee::select('code')->whereHas('licenses', function (Builder $query) use ($model_id, $model_type) {
            $query->whereHas('licensesAttributed', function (Builder $query) use ($model_id, $model_type) {
                $query->where('model_type', $model_type)
                    ->where('model_id', $model_id)
                    ->where('status_class', ActiveLicenseAttributedState::class);
            });
        })->pluck('code')->toArray();
    }
}
