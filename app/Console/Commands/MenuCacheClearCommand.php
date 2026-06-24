<?php

namespace App\Console\Commands;

use App\Services\MenuBuilderService;
use Illuminate\Console\Command;

class MenuCacheClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:cache-clear 
                            {menu? : The machine name of a specific menu to clear (optional)}
                            {--all : Clear cache for all menus}
                            {--rebuild : Rebuild cache after clearing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear menu cache to ensure updates are visible across all sessions';

    /**
     * Execute the console command.
     */
    public function handle(MenuBuilderService $menuService): int
    {
        if ($this->option('all') || ! $this->argument('menu')) {
            $this->info('Clearing cache for all menus...');
            $menuService->clearAllMenuCache();

            if ($this->option('rebuild')) {
                $this->info('Rebuilding all menu caches...');
                $menuService->rebuildAllCaches();
            }

            $this->info('✓ All menu caches cleared successfully!');
        } else {
            $menuName = $this->argument('menu');
            $this->info("Clearing cache for menu: {$menuName}");
            $menuService->clearCache($menuName);

            if ($this->option('rebuild')) {
                $this->info("Rebuilding cache for menu: {$menuName}");
                $menuService->build($menuName);
            }

            $this->info('✓ Menu cache cleared successfully!');
        }

        // Cache cleared successfully

        return Command::SUCCESS;
    }
}
