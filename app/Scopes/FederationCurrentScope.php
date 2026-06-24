<?php

namespace App\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class FederationCurrentScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        /*if (Auth::user() && Auth::user()->group()->first()->code == 'FEDERATION') {
            $builder->whereHas('users', function (Builder $query) {
                return $query->where('users.id', Auth::user()->id);
            });
        }*/
    }
}
