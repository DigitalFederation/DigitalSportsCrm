<?php

namespace App\Traits;

use App\Models\User;

trait CreatedUpdatedBy
{
    public static function bootCreatedUpdatedBy(): void
    {
        // updating created_by and updated_by when model is created
        static::creating(function ($model) {
            $model->created_by = auth()->check() ? auth()->user()->id : ($model->user_id ?? User::first()->id);
            $model->updated_by = auth()->check() ? auth()->user()->id : ($model->user_id ?? User::first()->id);
        });

        // updating updated_by when model is updated
        static::updating(function ($model) {
            $model->updated_by = auth()->check() ? auth()->user()->id : ($model->updated_by ?? User::first()->id);
        });
    }
}
