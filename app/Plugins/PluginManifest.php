<?php

namespace App\Plugins;

/**
 * Immutable description of a plugin.
 *
 * Every plugin's service provider returns one of these from manifest(); it is
 * what `php artisan plugins:list` and the plugin registry display.
 */
class PluginManifest
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $version = '0.0.0',
        public readonly string $description = '',
    ) {}
}
