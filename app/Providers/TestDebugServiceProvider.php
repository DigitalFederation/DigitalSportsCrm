<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class TestDebugServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->environment('testing')) {
            DB::listen(function ($query) {
                $sql = strtolower($query->sql);
                if (str_contains($sql, 'start transaction') ||
                    str_contains($sql, 'commit') ||
                    str_contains($sql, 'rollback')) {
                    Log::debug('DB Transaction: '.$query->sql);
                }
            });
        }
    }
}
