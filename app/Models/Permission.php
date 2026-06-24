<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $category
 * @property string|null $description
 * @property string $display_name
 * @property int $roles_count
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 */
class Permission extends \Spatie\Permission\Models\Permission
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'guard_name',
        'category',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'guard_name' => 'web',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'roles_count',
        'display_name',
    ];

    /**
     * Ensure guard_name is always set when creating new permissions
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($permission) {
            if (empty($permission->guard_name)) {
                $permission->guard_name = config('auth.defaults.guard', 'web');
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function routePermissions(): HasMany
    {
        return $this->hasMany(RoutePermission::class, 'permission_name', 'name');
    }

    public function getRolesCountAttribute(): int
    {
        return $this->roles()->count();
    }

    public function getDisplayNameAttribute(): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $this->name));
    }

    public function getCategoryDisplayNameAttribute(): string
    {
        return $this->category ? ucwords($this->category) : __('role_management.uncategorized');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeWithRoleCounts($query)
    {
        return $query->withCount('roles');
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

    public static function defaultPermissions(): array
    {
        return [
            'show all federations',
            'show federation',
            'create federation',
            'edit federation',
            'delete federation',

            'show all entities',
            'show entity',
            'create entity',
            'edit entity',
            'delete entity',

            'show all individuals',
            'show individual',
            'create individual',
            'edit individual',
            'delete individual',

            'show all memberships',
            'show membership',
            'create membership',
            'edit membership',
            'delete membership',

            'show all membership plans',
            'show membership plan',
            'create membership plan',
            'edit membership plan',
            'delete membership plan',
            'update membership status',

            'show all certifications',
            'show certification',
            'create certification',
            'edit certification',
            'delete certification',

        ];
    }
}
