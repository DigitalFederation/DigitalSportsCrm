<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleTemplate extends Model
{
    use CreatedUpdatedBy, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'permissions',
        'category',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPermissionsCountAttribute(): int
    {
        return count($this->permissions ?? []);
    }

    public function getDisplayNameAttribute(): string
    {
        return ucwords($this->name);
    }

    public function getCategoryDisplayNameAttribute(): string
    {
        return $this->category ? ucwords($this->category) : __('role_management.uncategorized');
    }

    public function scopeActive($query, bool $active = true)
    {
        return $query->where('is_active', $active);
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

    public function createRoleFromTemplate(string $roleName, ?string $roleDescription = null): Role
    {
        /** @var Role $role */
        $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
            'description' => $roleDescription ?? $this->description,
            'category' => $this->category,
            'created_by' => auth()->id(),
        ]);

        if (! empty($this->permissions)) {
            $permissions = Permission::whereIn('name', $this->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    public static function getAvailableCategories(): array
    {
        return static::distinct('category')
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }
}
