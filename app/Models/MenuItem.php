<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'menu_group_id',
        'parent_id',
        'committee_id',
        'name',
        'icon',
        'order',
        'route_name',
        'route_parameters',
        'active_patterns',
        'permissions',
        'selected_roles',
        'visibility_conditions',
        'visible',
        'badge_config',
        'translation_namespace',
        'metadata',
    ];

    protected $casts = [
        'route_parameters' => 'array',
        'active_patterns' => 'array',
        'permissions' => 'array',
        'selected_roles' => 'array',
        'visibility_conditions' => 'array',
        'visible' => 'boolean',
        'badge_config' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the menu this item belongs to
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get child menu items
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->orderBy('order');
    }

    /**
     * Get visible child menu items
     */
    public function visibleChildren(): HasMany
    {
        return $this->children()
            ->where('visible', true);
    }

    /**
     * Get the menu group this item belongs to
     */
    public function menuGroup(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class);
    }

    /**
     * Get the committee this item is associated with
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Scope to only visible items
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('visible', true);
    }

    /**
     * Scope to only root-level items
     */
    public function scopeRootLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to items for a specific committee
     */
    public function scopeForCommittee(Builder $query, $committeeId): Builder
    {
        return $query->where(function ($q) use ($committeeId) {
            $q->where('committee_id', $committeeId)
                ->orWhereNull('committee_id');
        });
    }

    /**
     * Scope to items in a specific group
     */
    public function scopeInGroup(Builder $query, $groupId): Builder
    {
        if ($groupId) {
            return $query->where(function ($q) use ($groupId) {
                $q->where('menu_group_id', $groupId)
                    ->orWhereNull('menu_group_id'); // Include ungrouped items
            });
        }

        // If no group specified, return all items
        return $query;
    }

    /**
     * Check if this menu item has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this menu item has visible children
     */
    public function hasVisibleChildren(): bool
    {
        return $this->visibleChildren()->exists();
    }

    /**
     * Generate the URL for this menu item
     */
    public function getUrl(): ?string
    {
        if (! $this->route_name) {
            return null;
        }

        try {
            if ($this->route_parameters) {
                return route($this->route_name, $this->route_parameters);
            }

            return route($this->route_name);
        } catch (\Exception $e) {
            // Log the error and return null for broken routes
            \Log::warning('Failed to generate URL for menu item', [
                'menu_item_id' => $this->id,
                'route_name' => $this->route_name,
                'route_parameters' => $this->route_parameters,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if this menu item should be marked as active
     */
    public function isActive(): bool
    {
        if (! $this->active_patterns) {
            return false;
        }

        // Ensure active_patterns is an array
        $patterns = $this->active_patterns;
        if (is_string($patterns)) {
            $patterns = json_decode($patterns, true) ?: [];
        }

        if (! is_array($patterns)) {
            return false;
        }

        $currentPath = request()->path();
        $segments = explode('/', $currentPath);

        $pathMatches = false;
        foreach ($patterns as $pattern) {
            // If pattern contains wildcard, use fnmatch against each segment
            if (str_contains($pattern, '*')) {
                foreach ($segments as $segment) {
                    if (fnmatch($pattern, $segment)) {
                        $pathMatches = true;
                        break 2;
                    }
                }
            } else {
                // Exact segment match
                if (in_array($pattern, $segments, true)) {
                    $pathMatches = true;
                    break;
                }
            }
        }

        if (! $pathMatches) {
            return false;
        }

        // If this menu item has a committee_id, also check that the URL's
        // filter[committee] matches this item's committee code
        if ($this->committee_id) {
            $urlCommittee = request()->input('filter.committee');
            if ($urlCommittee) {
                // Query committee code directly to avoid lazy loading
                $menuCommitteeCode = Committee::where('id', $this->committee_id)->value('code');

                return $menuCommitteeCode && strtoupper($urlCommittee) === $menuCommitteeCode;
            }

            // No committee filter in URL, so this committee menu should not be active
            return false;
        }

        return true;
    }

    /**
     * Check if user has permission to see this menu item
     */
    public function userCanAccess(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        // If no permissions specified, allow access
        if (! $this->permissions || empty($this->permissions)) {
            return true;
        }

        $user = auth()->user();

        // Ensure permissions is an array
        $permissions = $this->permissions;
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?: [];
        }

        if (! is_array($permissions)) {
            return true; // If permissions format is invalid, allow access
        }

        // Check if user has any of the specified permissions
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluate complex visibility conditions
     */
    public function evaluateVisibilityConditions(): bool
    {
        if (! $this->visibility_conditions) {
            return true;
        }

        // This can be extended to handle complex visibility logic
        // For now, return true for basic implementation
        return true;
    }

    /**
     * Check if this menu item should be displayed
     */
    public function shouldDisplay(): bool
    {
        return $this->visible
            && $this->userCanAccess()
            && $this->evaluateVisibilityConditions();
    }

    /**
     * Get the display text for this menu item.
     *
     * Names may be stored as readable strings (admin UI / staging seed) or as
     * translation keys (e.g. seeded from config/menu.php). __() resolves keys and
     * returns readable literals unchanged, so both styles render correctly.
     */
    public function getDisplayText(): string
    {
        return $this->name ? (string) __($this->name) : '';
    }

    /**
     * Legacy method for backward compatibility
     *
     * @deprecated Use getDisplayText() instead
     */
    public function getTranslationKey(): string
    {
        return $this->getDisplayText();
    }

    /**
     * Validate that the route exists
     */
    public function validateRoute(): bool
    {
        if (! $this->route_name) {
            return true; // No route is valid (might be a parent item)
        }

        return Route::has($this->route_name);
    }

    /**
     * Boot method to handle cache clearing
     */
    protected static function boot()
    {
        parent::boot();

        // Clear menu cache when menu items change
        static::saved(function ($menuItem) {
            // Load the menu relationship if not already loaded
            if (! $menuItem->relationLoaded('menu')) {
                $menuItem->load('menu');
            }

            if ($menuItem->menu) {
                $menuItem->menu->clearCache();
            }
        });

        static::deleted(function ($menuItem) {
            // Load the menu relationship if not already loaded
            if (! $menuItem->relationLoaded('menu')) {
                $menuItem->load('menu');
            }

            if ($menuItem->menu) {
                $menuItem->menu->clearCache();
            }
        });
    }
}
