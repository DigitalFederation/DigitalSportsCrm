<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'machine_name',
        'description',
        'active',
        'metadata',
    ];

    protected $casts = [
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get all menu items for this menu
     */
    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Get all groups for this menu
     */
    public function groups(): HasMany
    {
        return $this->hasMany(MenuGroup::class)
            ->orderBy('order');
    }

    /**
     * Get active groups for this menu
     */
    public function activeGroups(): HasMany
    {
        return $this->groups()
            ->where('active', true);
    }

    /**
     * Get only root-level menu items (no parent)
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    /**
     * Get visible root-level menu items
     */
    public function visibleRootItems(): HasMany
    {
        return $this->rootItems()
            ->where('visible', true);
    }

    /**
     * Scope to only active menus
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Find menu by machine name
     */
    public static function findByMachineName(string $machineName): ?self
    {
        return static::where('machine_name', $machineName)
            ->active()
            ->first();
    }

    /**
     * Get the cache key for this menu
     */
    public function getCacheKey(): string
    {
        return "menu.{$this->machine_name}.structure";
    }

    /**
     * Clear the menu cache
     */
    public function clearCache(): void
    {
        // Use the MenuBuilderService to clear cache properly
        app(\App\Services\MenuBuilderService::class)->clearCache($this->machine_name);

        // Also clear the legacy cache key for backward compatibility
        cache()->forget($this->getCacheKey());
    }

    /**
     * Boot method to handle cache clearing
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($menu) {
            $menu->clearCache();
        });

        static::deleted(function ($menu) {
            $menu->clearCache();
        });
    }
}
