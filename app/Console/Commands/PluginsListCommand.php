<?php

namespace App\Console\Commands;

use App\Plugins\PluginManager;
use Illuminate\Console\Command;

/**
 * List the plugins currently installed and auto-discovered.
 */
class PluginsListCommand extends Command
{
    protected $signature = 'plugins:list';

    protected $description = 'List installed plugins';

    public function handle(PluginManager $plugins): int
    {
        $rows = [];

        foreach ($plugins->all() as $plugin) {
            $manifest = $plugin->manifest();
            $rows[] = [$manifest->id, $manifest->name, $manifest->version, $manifest->description];
        }

        if ($rows === []) {
            $this->info('No plugins installed.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Name', 'Version', 'Description'], $rows);

        return self::SUCCESS;
    }
}
