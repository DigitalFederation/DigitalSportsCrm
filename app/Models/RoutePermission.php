<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePermission extends Model
{
    use CreatedUpdatedBy, HasFactory;

    protected $fillable = [
        'route_pattern',
        'permission_name',
        'middleware',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'middleware' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_name', 'name');
    }

    public function getDisplayRouteAttribute(): string
    {
        return str_replace(['{', '}'], [':', ''], $this->route_pattern);
    }

    public function getMiddlewareListAttribute(): string
    {
        if (empty($this->middleware)) {
            return __('role_management.no_middleware');
        }

        return implode(', ', $this->middleware);
    }

    public function scopeActive($query, bool $active = true)
    {
        return $query->where('is_active', $active);
    }

    public function scopeByPermission($query, string $permissionName)
    {
        return $query->where('permission_name', $permissionName);
    }

    public function scopeByRoute($query, string $routePattern)
    {
        return $query->where('route_pattern', 'like', "%{$routePattern}%");
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('route_pattern', 'like', "%{$search}%")
                ->orWhere('permission_name', 'like', "%{$search}%");
        });
    }

    public static function getRoutesByPermission(string $permissionName): array
    {
        return static::where('permission_name', $permissionName)
            ->where('is_active', true)
            ->pluck('route_pattern')
            ->toArray();
    }

    public static function getPermissionsByRoute(string $routePattern): array
    {
        return static::where('route_pattern', $routePattern)
            ->where('is_active', true)
            ->pluck('permission_name')
            ->toArray();
    }

    public static function getUniqueRoutePatterns(): array
    {
        return static::distinct('route_pattern')
            ->where('is_active', true)
            ->orderBy('route_pattern')
            ->pluck('route_pattern')
            ->toArray();
    }

    public static function getUniquePermissions(): array
    {
        return static::distinct('permission_name')
            ->where('is_active', true)
            ->orderBy('permission_name')
            ->pluck('permission_name')
            ->toArray();
    }
}
