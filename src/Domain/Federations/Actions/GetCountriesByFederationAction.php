<?php

namespace Domain\Federations\Actions;

use App\Models\User;
use Illuminate\Support\Collection;

class GetCountriesByFederationAction
{
    public static function execute(): Collection
    {
        $countries = collect([]);
        /** @var User|null $user */
        $user = \Auth::user();
        $federations = $user?->federations ?? collect([]);
        foreach ($federations as $federation) {
            $countries->push($federation->country);
        }

        return $countries;

    }

}
