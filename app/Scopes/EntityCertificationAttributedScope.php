<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class EntityCertificationAttributedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::user() && Auth::user()->group()->first()->code == 'ENTITY') {
            $entityId = Auth::user()->entities()->first()->id;

            $builder->whereHas('individual', function ($query) use ($entityId) {
                $query->whereHas('entities', function ($subQuery) use ($entityId) {
                    $subQuery->where('entity_id', $entityId);
                });
            });
        }
    }
}
