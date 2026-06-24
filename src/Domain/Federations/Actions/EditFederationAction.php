<?php

namespace Domain\Federations\Actions;

use Domain\Federations\DataTransferObject\FederationData;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Support\Facades\DB;

class EditFederationAction
{
    /**
     * @throws Exception
     */
    public function __invoke(FederationData $data, int $id, array $zoneIds = []): bool
    {

        DB::beginTransaction();

        try {
            $federation = Federation::find($id);
            $updated = $federation->update((array) $data);

            if (! empty($data->logo)) {
                $federation->clearMediaCollection('profile');
                $federation->addMedia($data->logo)->toMediaCollection('profile', 'public');
            }

            // Sync zones relationship
            if ($federation) {
                $federation->zones()->sync($zoneIds);
            }

            if ($updated) {
                activity('Federation')
                    ->performedOn($federation)
                    ->event('updated')
                    ->withProperties((array) $data)
                    ->log('Federation profile updated');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $updated;
    }
}
