<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class CheckRoutesOnViewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-routes-on-views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if all route() calls in views have corresponding routes in web.php';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $resourcePath = resource_path('views');
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resourcePath));
        $regex = new RegexIterator($iterator, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

        $definedRoutes = collect(Route::getRoutes())->map(function ($route) {
            return $route->getName();
        })->filter()->values()->all();

        $missingRoutes = [];

        foreach ($regex as $file) {
            $content = file_get_contents($file[0]);
            preg_match_all('/route\([\'"]([^\'"]+)[\'"]\)/', $content, $matches);

            foreach ($matches[1] as $routeName) {
                if (! in_array($routeName, $definedRoutes)) {
                    $missingRoutes[$file[0]][] = $routeName;
                }
            }
        }

        if (count($missingRoutes) > 0) {
            $this->alert('Missing routes:');
            foreach ($missingRoutes as $file => $routes) {
                foreach ($routes as $route) {
                    $this->line("{$route} in {$file}");
                }
            }

            return 1;
        } else {
            $this->info('All routes are defined.');

            return 0;
        }
    }
}
