<?php

namespace App\Scopes;

use Domain\Individuals\States\ActiveIndividualEntityState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IndividualsFromEntityScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @return Builder|void
     */
    public function apply(Builder $builder, Model $model)
    {

        if (auth()->user() && empty(auth()->user()->group()->first())) {
            return $builder->where('id', 0);
        }

        if (auth()->user() && auth()->user()->group()->first()['code'] == 'ENTITY') {
            $builder->whereHas('individualEntities', function ($q) {
                return $q->where('entity_id', auth()->user()->entities()->first()->id)->where('status_class', ActiveIndividualEntityState::class);
            });
        }

    }
}
