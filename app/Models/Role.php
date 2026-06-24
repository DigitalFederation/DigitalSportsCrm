<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string|null $category
 * @property string|null $description
 * @property string|null $display_name
 * @property int|null $users_count
 * @property int|null $permissions_count
 * @property bool|null $is_protected
 * @property string|null $protection_level
 */
class Role extends \Spatie\Permission\Models\Role
{
    use CreatedUpdatedBy;
    use LogsActivity;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'category',
        'scope',
        'is_protected',
        'protection_level',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'guard_name' => 'web',
    ];

    /**
     * Override the users() relationship to bypass Spatie Permission issues
     * This is a complete override to avoid the "Class name must be a valid object or a string" error
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        // Get the User model class directly from auth config to avoid Spatie's getModelForGuard issues
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        return $this->morphedByMany(
            $userModel,
            'model',
            config('permission.table_names.model_has_roles', 'model_has_roles'),
            'role_id',
            config('permission.column_names.model_morph_key', 'model_id')
        );
    }

    /**
     * Ensure guard_name is always set when creating new roles
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->guard_name)) {
                $role->guard_name = config('auth.defaults.guard', 'web');
            }
        });
    }

    protected $casts = [
        'is_protected' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Removed $appends to prevent circular dependency issues
    // These attributes can be accessed manually when needed: $role->users_count, $role->protection_info

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'category', 'is_protected', 'protection_level'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getUsersCountAttribute(): int
    {
        // Check if users_count was loaded with withCount()
        if (isset($this->attributes['users_count'])) {
            return (int) $this->attributes['users_count'];
        }

        // Check if users relationship is loaded to avoid N+1 queries
        if ($this->relationLoaded('users')) {
            return $this->users->count();
        }

        // Fallback to query if relationship not loaded
        return $this->users()->count();
    }

    public function getPermissionsCountAttribute(): int
    {
        // Check if permissions_count was loaded with withCount()
        if (isset($this->attributes['permissions_count'])) {
            return (int) $this->attributes['permissions_count'];
        }

        // Check if permissions relationship is loaded to avoid N+1 queries
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->count();
        }

        // Fallback to query if relationship not loaded
        return $this->permissions()->count();
    }

    public function getProtectionInfoAttribute(): array
    {
        return [
            'is_protected' => $this->is_protected,
            'protection_level' => $this->protection_level,
            'can_delete' => ! $this->is_protected && $this->users_count === 0,
            'can_modify' => ! $this->is_protected || $this->protection_level !== 'system',
        ];
    }

    public function isSystemRole(): bool
    {
        return $this->protection_level === 'system';
    }

    public function isAdminRole(): bool
    {
        return $this->protection_level === 'admin';
    }

    public function isProtected(): bool
    {
        return $this->is_protected;
    }

    public function getDisplayNameAttribute(): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $this->name));
    }

    public function getCategoryDisplayNameAttribute(): string
    {
        return $this->category ? ucwords($this->category) : __('role_management.uncategorized');
    }

    public function getLastModifiedAttribute(): ?string
    {
        return $this->updated_at?->diffForHumans();
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByScope($query, string $scope)
    {
        return $query->where('scope', $scope);
    }

    public function scopeProtected($query, bool $protected = true)
    {
        return $query->where('is_protected', $protected);
    }

    public function scopeByProtectionLevel($query, string $level)
    {
        return $query->where('protection_level', $level);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeWithUserCounts($query)
    {
        return $query->withCount('users');
    }

    public function scopeWithPermissionCounts($query)
    {
        return $query->withCount('permissions');
    }

    public static function getAvailableCategories(): array
    {
        return static::distinct('category')
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    public static function getAvailableScopes(): array
    {
        return ['system', 'federation', 'entity', 'individual'];
    }

    public static function getProtectionLevels(): array
    {
        return [
            'system' => __('role_management.protection_levels.system'),
            'admin' => __('role_management.protection_levels.admin'),
            'user' => __('role_management.protection_levels.user'),
        ];
    }
}
