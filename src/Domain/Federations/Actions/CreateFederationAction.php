<?php

namespace Domain\Federations\Actions;

use Domain\Federations\DataTransferObject\FederationData;
use Domain\Federations\Models\Federation;

class CreateFederationAction
{
    public function __invoke(FederationData $data, array $zoneIds = []): Federation
    {

        // $data->member_code = UtilityMethods::generateUniqueFederationCode();
        $federation = Federation::create((array) $data);

        if (! empty($data->logo)) {
            $federation->addMedia($data->logo)->toMediaCollection('logo', 'public');
        }

        // Attach zones to the federation
        if (! empty($zoneIds)) {
            $federation->zones()->attach($zoneIds);
        }

        if ($federation) {
            activity('Federation')
                ->performedOn($federation)
                ->event('created')
                ->withProperties((array) $data)
                ->log('Federation created');
        }

        return $federation;
    }
}
