<?php

namespace Domain\Licenses\Scopes;

use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExcludeInternationalScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Filters out international items based on committee.is_international flag.
     * International committees (DIVING, SCIENTIFIC) have is_international = true.
     * Non-international committees (SPORT, DIVINGSERVICES) have is_international = false.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Skip the scope for ADMIN users
        if (auth()->check() && auth()->user()->hasGroup('ADMIN')) {
            return;
        }

        if ($model instanceof LicenseAttributed) {
            // For LicenseAttributed, filter through license->committee relationship
            $builder->whereHas('license', function (Builder $query) {
                $query->whereHas('committee', function (Builder $committeeQuery) {
                    $committeeQuery->where('is_international', false);
                });
            });
        } else {
            // For License, Certification models - filter through committee relationship
            $builder->whereHas('committee', function (Builder $query) {
                $query->where('is_international', false);
            });
        }
    }
}
