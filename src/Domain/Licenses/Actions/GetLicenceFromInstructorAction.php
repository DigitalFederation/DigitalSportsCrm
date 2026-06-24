<?php

namespace Domain\Licenses\Actions;

use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GetLicenceFromInstructorAction
{
    /**
     * Undocumented function
     *
     * @param  Collection  $instructors  [1,2,3]
     */
    public function __invoke(Collection $instructors, ?int $federation_id = null)
    {
        if ($instructors->isNotEmpty()) {

            $licenses = LicenseAttributed::whereHas('owner', function (Builder $query) use ($instructors) {
                $query->where('model_type', 'individual')
                    ->whereIn('model_id', $instructors->pluck('id'));
            });

            if (! empty($federation_id)) {
                $federation = Federation::findOrFail($federation_id);
                $licenses = $licenses->whereHas('federation', function (Builder $q) use ($federation) {
                    $q->where('country_id', $federation->country_id);
                });
            }

            $licenses = $licenses->get()->unique('license_id');

        } else {
            return null;
        }

        return $licenses;
    }
}
