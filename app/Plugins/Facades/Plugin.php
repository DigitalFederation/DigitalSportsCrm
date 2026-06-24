<?php

namespace App\Plugins\Facades;

use App\Plugins\PluginManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(\App\Plugins\PluginServiceProvider $plugin)
 * @method static array all()
 * @method static \App\Plugins\PluginServiceProvider|null get(string $id)
 * @method static bool has(string $id)
 * @method static array permissions()
 * @method static array menu()
 *
 * @see \App\Plugins\PluginManager
 */
class Plugin extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PluginManager::class;
    }
}
