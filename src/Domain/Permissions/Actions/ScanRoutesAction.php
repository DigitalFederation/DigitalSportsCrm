<?php

namespace Domain\Permissions\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ScanRoutesAction
{
    public static function execute(array $filters = []): Collection
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'methods' => $route->methods(),
                'middleware' => $route->middleware(),
                'action' => $route->getActionName(),
            ];
        });

        // Filter out routes without names and system routes
        $routes = $routes->filter(function ($route) {
            // Must have a name
            if (empty($route['name'])) {
                return false;
            }

            // Exclude common system/dev route patterns
            $excludedPatterns = [
                'sanctum.',
                'ignition.',
                'debugbar.',
                'horizon.',
                'pulse.',
                'telescope.',
                '__clockwork',
                'livewire.',
                'filament.',
            ];

            foreach ($excludedPatterns as $pattern) {
                if (str_starts_with($route['name'], $pattern) || str_starts_with($route['uri'], $pattern)) {
                    return false;
                }
            }

            return true;
        });

        // Apply filters
        if (! empty($filters['prefix'])) {
            $routes = $routes->filter(function ($route) use ($filters) {
                return str_starts_with($route['uri'], $filters['prefix']);
            });
        }

        if (! empty($filters['middleware'])) {
            $routes = $routes->filter(function ($route) use ($filters) {
                return in_array($filters['middleware'], $route['middleware']);
            });
        }

        if (! empty($filters['method'])) {
            $routes = $routes->filter(function ($route) use ($filters) {
                return in_array(strtoupper($filters['method']), $route['methods']);
            });
        }

        if (! empty($filters['search'])) {
            $routes = $routes->filter(function ($route) use ($filters) {
                $search = strtolower($filters['search']);

                return str_contains(strtolower($route['uri']), $search) ||
                       str_contains(strtolower($route['name'] ?? ''), $search);
            });
        }

        return $routes->sortBy('uri')->values();
    }
}
