<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class IndividualsFromFederationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @return Builder|void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        $group = $user->group()->first();

        if (! $group) {
            return $builder->whereRaw('1 = 0');
        }

        if ($group['code'] == 'FEDERATION') {
            $federation = $user->federations()->first();
            if ($federation) {
                $builder->whereHas('federations', function ($q) use ($federation) {
                    return $q->where('federation.id', $federation->id);
                });
            } else {
                $builder->whereRaw('1 = 0');
            }
        }
    }
}
