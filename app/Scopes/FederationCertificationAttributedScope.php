<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class FederationCertificationAttributedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::user() && Auth::user()->group()->first()->code == 'FEDERATION') {
            $federation = Auth::user()->federations()->first();
            if ($federation) {
                if ($federation->parent_id === null) {
                    // Main federation - show only main federation records
                    $builder->where('federation_id', $federation->id);
                } else {
                    // Local federation - show own AND parent (main) federation records
                    $builder->whereIn('federation_id', [$federation->id, $federation->parent_id]);
                }
            }
        }
    }
}
