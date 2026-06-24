<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BelongsToFederationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::user() && Auth::user()->group()->first()->code == 'FEDERATION') {
            $my_federation = Auth::user()->federations()->first();
            // also find the parent federation if the user is a subfederation
            if ($my_federation) {
                if ($my_federation->parent_id == null) {
                    $builder->where('federation_id', $my_federation->id);
                } else {
                    $builder->whereIn('federation_id', [$my_federation->id, $my_federation->parent_id]);
                }
            }
        }
    }
}
