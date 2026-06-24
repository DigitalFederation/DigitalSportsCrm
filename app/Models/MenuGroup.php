<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'name',
        'machine_name',
        'description',
        'icon',
        'order',
        'is_default',
        'active',
        'visibility_type',
        'required_roles',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'active' => 'boolean',
        'order' => 'integer',
        'required_roles' => 'array',
    ];

    /**
     * Get the menu this group belongs to
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the menu items in this group
     */
    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->orderBy('order');
    }

    /**
     * Get visible menu items in this group
     */
    public function visibleMenuItems(): HasMany
    {
        return $this->menuItems()
            ->where('visible', true);
    }

    /**
     * Scope to only active groups
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope to only default groups
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default group for a menu
     */
    public static function getDefaultForMenu(int $menuId): ?self
    {
        return static::where('menu_id', $menuId)
            ->active()
            ->default()
            ->first();
    }

    /**
     * Set this group as the default for its menu
     */
    public function setAsDefault(): void
    {
        // Remove default from other groups in same menu
        static::where('menu_id', $this->menu_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Check if this group has any items
     */
    public function hasItems(): bool
    {
        return $this->menuItems()->exists();
    }

    /**
     * Get item count for display
     */
    public function getItemCountAttribute(): int
    {
        return $this->menuItems()->count();
    }

    /**
     * Get visible item count for display
     */
    public function getVisibleItemCountAttribute(): int
    {
        return $this->visibleMenuItems()->count();
    }

    /**
     * Check if a user can access this group
     */
    public function userCanAccess($user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        // If visibility is set to 'all', everyone can access
        if ($this->visibility_type === 'all') {
            return true;
        }

        // Check if user has any of the required roles
        if ($this->visibility_type === 'roles' && ! empty($this->required_roles)) {
            foreach ($this->required_roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        // Default to true if no specific restrictions
        return true;
    }

    /**
     * Scope to filter groups accessible by a user
     */
    public function scopeAccessibleBy(Builder $query, $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            // No user, only show groups with 'all' visibility
            return $query->where('visibility_type', 'all');
        }

        return $query->where(function ($q) use ($user) {
            // Groups visible to all
            $q->where('visibility_type', 'all')
              // Or groups with role restrictions where user has the role
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('visibility_type', 'roles')
                        ->where(function ($q3) use ($user) {
                            $userRoles = $user->roles->pluck('name')->toArray();
                            foreach ($userRoles as $role) {
                                $q3->orWhereJsonContains('required_roles', $role);
                            }
                        });
                });
        });
    }

    /**
     * Boot method to handle cache clearing
     */
    protected static function boot()
    {
        parent::boot();

        // Clear menu cache when groups change
        static::saved(function ($group) {
            if ($group->relationLoaded('menu')) {
                $group->menu->clearCache();
            } else {
                $group->load('menu');
                if ($group->menu) {
                    $group->menu->clearCache();
                }
            }
        });

        static::deleted(function ($group) {
            if ($group->relationLoaded('menu')) {
                $group->menu->clearCache();
            } else {
                $group->load('menu');
                if ($group->menu) {
                    $group->menu->clearCache();
                }
            }
        });

        // Ensure only one default per menu
        static::saving(function ($group) {
            if ($group->is_default && $group->isDirty('is_default')) {
                static::where('menu_id', $group->menu_id)
                    ->where('id', '!=', $group->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
